/* Arezoo dashboard: actions with confirmation, gallery picker, detail rows, sortable categories. */
(function ($) {
  'use strict';
  var A = window.AY_ADMIN || { t: {} };
  function flash(msg, ok) { var $f = $('#ayFlash'); if (!$f.length) return; $f.removeAttr('hidden').toggleClass('is-bad', !ok).text(msg); clearTimeout(flash.t); flash.t = setTimeout(function () { $f.attr('hidden', true); }, 6000); }
  function post(data, $btn) {
    data.nonce = A.nonce;
    if ($btn) { $btn.prop('disabled', true).data('label', $btn.text()).text(A.t.working); }
    return $.post(A.ajax, data).done(function (r) {
      if (r && r.success) { flash((r.data && r.data.message) || A.t.done, true); if (r.data && r.data.reload) setTimeout(function () { location.reload(); }, 700); }
      else flash((r && r.data && r.data.message) || A.t.error, false);
    }).fail(function (x) { flash((x.responseJSON && x.responseJSON.data && x.responseJSON.data.message) || A.t.error, false); })
      .always(function () { if ($btn) $btn.prop('disabled', false).text($btn.data('label')); });
  }
  /* confirmation dialog (built into the page) */
  function confirmBox(title, text, okLabel, cb) {
    var $m = $('#ayModal'); if (!$m.length) { cb(); return; }
    $('#ayModalT').text(title); $('#ayModalP').text(text); $('#ayModalOk').text(okLabel).off('click').on('click', function () { $m.attr('hidden', true); cb(); });
    $('#ayModalNo').off('click').on('click', function () { $m.attr('hidden', true); });
    $m.removeAttr('hidden'); $('#ayModalOk').trigger('focus');
  }
  $(document).on('keydown', function (e) { if (e.key === 'Escape') $('#ayModal').attr('hidden', true); });

  $(document).on('click', '[data-ay-act]', function () {
    var $b = $(this), data = { action: $b.data('ay-act') };
    ['id', 'area', 'n', 'force'].forEach(function (k) { if ($b.data(k) !== undefined) data[k] = $b.data(k); });
    if (data.action === 'ay_build') data.samples = $('#aySamples').is(':checked') ? 1 : 0;
    var run = function () { post(data, $b); };
    if ($b.data('confirm')) confirmBox($b.data('confirm-t') || $b.text(), $b.data('confirm'), $b.data('confirm-ok') || $b.text(), run); else run();
  });

  /* ---- categories ---- */
  $('.ay-terms').each(function () {
    var $list = $(this), tax = $list.data('tax');
    if ($.fn.sortable) $list.sortable({ handle: '.ay-drag', axis: 'y', update: function () { post({ action: 'ay_terms', tax: tax, do: 'order', ids: $list.children().map(function () { return $(this).data('id'); }).get() }); } });
    $list.on('change', 'input', function () { post({ action: 'ay_terms', tax: tax, do: 'rename', id: $(this).closest('.ay-term').data('id'), name: this.value }); });
    $list.on('click', '.ay-x', function () { var id = $(this).closest('.ay-term').data('id'); confirmBox('حذف دسته', A.t.sure_del, 'حذف کن', function () { post({ action: 'ay_terms', tax: tax, do: 'delete', id: id }); }); });
  });
  $(document).on('click', '[data-ay-term-add]', function () { var $b = $(this), $i = $b.prev('input'); post({ action: 'ay_terms', tax: $b.data('ay-term-add'), do: 'add', name: $i.val() }, $b); });
  $(document).on('keydown', '.ay-term-add input', function (e) { if (e.key === 'Enter') { e.preventDefault(); $(this).next('button').trigger('click'); } });

  /* ---- media pickers (work gallery, custom sketches) ---- */
  function picker(listSel, inputSel, addSel, clearSel, dark) {
    var $list = $(listSel), $input = $(inputSel); if (!$list.length) return;
    var sync = function () { $input.val($list.children().map(function () { return $(this).data('id'); }).get().join(',')); };
    if ($.fn.sortable) $list.sortable({ update: sync });
    $(document).on('click', addSel, function (e) {
      e.preventDefault();
      var frame = wp.media({ title: A.t.pick, button: { text: A.t.use }, multiple: true, library: { type: 'image' } });
      frame.on('select', function () {
        frame.state().get('selection').each(function (att) {
          var a = att.toJSON(), src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url);
          if ($list.children('[data-id="' + a.id + '"]').length) return;
          $list.append($('<span class="ay-ed-thumb' + (dark ? ' ay-ed-thumb-dark' : '') + '"/>').attr('data-id', a.id).append($('<img alt="">').attr('src', src)).append('<button type="button" class="ay-ed-x" aria-label="حذف">×</button>'));
        });
        sync();
      });
      frame.open();
    });
    $list.on('click', '.ay-ed-x', function () { $(this).parent().remove(); sync(); });
    if (clearSel) $(document).on('click', clearSel, function (e) { e.preventDefault(); $list.empty(); sync(); });
  }
  picker('#ay_gallery_list', '#ay_gallery', '#ay_gallery_add', '#ay_gallery_clear', false);
  picker('#ay_custom_list', '#ay_custom', '#ay_custom_add', null, true);

  /* ---- free detail rows of a work ---- */
  var $specs = $('#ay_specs_list');
  if ($specs.length) {
    var n = $specs.children().length;
    if ($.fn.sortable) $specs.sortable({ handle: '.ay-ed-drag', axis: 'y' });
    $('#ay_specs_add').on('click', function () { $specs.append($('#ay_specs_tpl').html().replace(/__i__/g, 'n' + (n++))); $specs.children().last().find('input').first().trigger('focus'); });
    $specs.on('click', '.ay-ed-x', function () { $(this).closest('.ay-ed-row').remove(); });
  }


  /* ---- page curtain: single logo picker + live preview ---- */
  $(document).on('click', '#ay_logo_add', function (e) {
    e.preventDefault();
    var frame = wp.media({ title: A.t.pick, button: { text: A.t.use }, multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON(), src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url);
      $('#ay_logo_id').val(a.id).trigger('change');
      $('#ay_logo_list').empty().append($('<span class="ay-ed-thumb ay-ed-thumb-dark"/>').attr('data-id', a.id).append($('<img alt="">').attr('src', src)).append('<button type="button" class="ay-ed-x" aria-label="حذف">×</button>'));
      $('#ayCvStage .ay-cv-logo').replaceWith($('<img class="ay-cv-logo ay-cv-img" alt="">').attr('src', a.url));
    });
    frame.open();
  });
  $('#ay_logo_list').on('click', '.ay-ed-x', function () { $(this).parent().remove(); $('#ay_logo_id').val(0).trigger('change'); $('#ayCvStage .ay-cv-logo').replaceWith('<span class="ay-cv-logo ay-cv-mark"></span>'); });
  var $stage = $('#ayCvStage');
  if ($stage.length) {
    var cv = $stage.find('.ay-cv')[0], $form = $stage.closest('form'), timers = [], page = 1;
    var EASE = { lux: 'cubic-bezier(.7,0,.2,1)', soft: 'cubic-bezier(.16,1,.3,1)', natural: 'cubic-bezier(.22,.61,.36,1)', linear: 'linear' };
    var val = function (n) { return $form.find('[name="' + n + '"]').val(); };
    var on = function (n) { return $form.find('[name="' + n + '"]').is(':checked'); };
    var apply = function () {
      var st = cv.style, set = function (k, v) { if (v === '' || v == null) st.removeProperty(k); else st.setProperty(k, v); };
      cv.setAttribute('data-style', $form.find('[name="style"]:checked').val() || 'frost');
      set('--cv-tint', val('tint')); set('--cv-op', (+val('tint_op') || 0) / 100); set('--cv-blur', (+val('blur') || 0) + 'px'); set('--cv-sat', (+val('sat') || 0) + '%'); set('--cv-bri', (+val('bright') || 100) + '%');
      set('--cv-dur', (+val('dur') || 750) + 'ms'); set('--cv-ease', EASE[val('ease')] || EASE.lux); set('--cv-logo', (+val('logo_size') || 110) + 'px'); set('--cv-ink', val('logo_color')); set('--cv-line', val('line_color'));
      cv.className = 'ay-cv is-idle ay-cv-fx-' + (val('logo_fx') || 'breathe') + (on('edge') ? ' ay-cv-edge' : '');
      $(cv).find('.ay-cv-logo').toggle(on('logo')); $(cv).find('.ay-cv-name').toggle(on('name')).text(val('name_text') || 'Arezoo Yousefi'); $(cv).find('.ay-cv-line').toggle(on('line'));
    };
    var play = function () {
      timers.forEach(clearTimeout); timers = []; apply();
      var dur = +val('dur') || 750, hold = +val('hold') || 0;
      cv.style.setProperty('--cv-x', '30%'); cv.style.setProperty('--cv-y', '75%');
      cv.classList.remove('is-idle'); void cv.offsetWidth; cv.classList.add('is-cover');
      timers.push(setTimeout(function () { page = page === 1 ? 2 : 1; $stage.find('.ay-cv-page').each(function () { this.hidden = +this.getAttribute('data-n') !== page; }); cv.classList.remove('is-cover'); cv.classList.add('is-reveal'); }, dur + hold + 500));
      timers.push(setTimeout(function () { cv.classList.remove('is-reveal'); cv.classList.add('is-idle'); }, dur + hold + 500 + dur * 1.3 + 160));
    };
    var deb = 0; $form.on('input change ay:color', 'input,select', function () { clearTimeout(deb); deb = setTimeout(play, 350); });
    $('#ayCvPlay').on('click', play); setTimeout(play, 700);
  }

  /* ---- settings niceties ---- */
  $('.ay-range input[type=range]').on('input', function () { $(this).closest('.ay-range').find('output').text(this.value + ($(this).data('unit') || '')); });
  if ($.fn.wpColorPicker) $('.ay-color').wpColorPicker({ change: function () { var el = this; setTimeout(function () { $(el).trigger('ay:color'); }, 30); }, clear: function () { $(this).closest('.wp-picker-container').find('.ay-color').trigger('ay:color'); } });
})(jQuery);
