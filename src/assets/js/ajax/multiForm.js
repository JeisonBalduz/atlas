import { clasesInputs, clasesInputsError } from "./inputs.js";
// next step
export function formulariomultiple(datos, alerta, contenido){
  $(document).on('click', datos, function () {
    var parent_fieldset = $(this).parents('fieldset');
    var next_step = true;
    var current_active_step = $(this).parents('.f1').find('.f1-step.active');
    var progress_line = $(this).parents('.f1').find('.f1-progress-line');

    // next step
    $(document).on('click', datos, function () {
      var parent_fieldset = $(this).parents('fieldset');
      var next_step = true;
      var current_active_step = $(this).parents('.f1').find('.f1-step.active');
      var progress_line = $(this).parents('.f1').find('.f1-progress-line');

      parent_fieldset.find().each(function () {
        if (!$(this).hasClass('ignore-validation')) {
          if (!$(this).hasClass('cumplido') && !$(this).hasClass('cumplidoNormal')) {
            clasesInputsError($(this));
            $(alerta).empty();
            $(alerta).html(contenido);
            $(alerta).hide();
            $(alerta).slideDown("slow");

            setTimeout(function () {
              $(alerta).slideUp("slow");
            }, 10000);
            next_step = false;
          } else {
            clasesInputs($(this));
          }
        }
      });

      if (next_step) {
        parent_fieldset.fadeOut(400, function () {
          current_active_step.removeClass('active').addClass('activated').next().addClass('active');
          bar_progress(progress_line, 'right');
          $(this).next().fadeIn();
          scroll_to_class($('.f1'), 20);
        });
      }
    });

    // previous step
    $('.f1 .btn-previous').on('click', function () {
      var current_active_step = $(this).parents('.f1').find('.f1-step.active');
      var progress_line = $(this).parents('.f1').find('.f1-progress-line');

      $(this).parents('fieldset').fadeOut(400, function () {
        current_active_step.removeClass('active').prev().removeClass('activated').addClass('active');
        bar_progress(progress_line, 'left');
        $(this).prev().fadeIn();
        scroll_to_class($('.f1'), 20);
      });
    });

    // submit
  });
}

function scroll_to_class(element_class, removed_height) {
  var scroll_to = $(element_class).offset().top - removed_height;
  if ($(window).scrollTop() != scroll_to) {
    $('html, body').stop().animate({ scrollTop: scroll_to }, 0);
  }
}

function bar_progress(progress_line_object, direction) {
  var number_of_steps = progress_line_object.data('number-of-steps');
  var now_value = progress_line_object.data('now-value');
  var new_value = 0;
  if (direction == 'right') {
    new_value = now_value + (100 / number_of_steps);
  } else if (direction == 'left') {
    new_value = now_value - (100 / number_of_steps);
  }
  progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);
}

jQuery(document).ready(function () {
  $('#top-navbar-1').on('shown.bs.collapse', function () {
    $.backstretch("resize");
  });
  $('#top-navbar-1').on('hidden.bs.collapse', function () {
    $.backstretch("resize");
  });

  $('.f1 fieldset:first').fadeIn('slow');

  $('.f1 input[type="text"], .f1 input[type="password"], .f1 textarea, .f1 select, .f1 input[type="number"]').on('focus', function () {
    $(this).removeClass('input-error');
  });

});
