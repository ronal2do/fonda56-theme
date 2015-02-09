/*!
 * Copyright (c) 2013 Smart IO Labs
 * Project repository: http://smartiolabs.com
 * license: Is not allowed to use any part of the code.
 */
var $ = jQuery;
var smpush_currcount=0, smpush_percent=0, smpush_google_open = 1, smpush_firstrun = 1, smpush_feedback_open = 1, smpush_feedback_google = 1;
var smpush_pro_currcount=0, smpush_pro_percent=0, smpush_lastid=0, smpush_resum_timer;
$(document).ready(function() {
  $("#smpush_model_select").change(function(){
    $('.smpush_apidesc').hide();
    $('.smpush_method_'+$(this).val()).show();
  });
  $('#smio-submit').click(function(){
    var form = $(this).parents('form');
    if(!validateForm(form))
      return false;
  });
  $('#push-token-list td span').click(function(){
    $(this).attr('style', 'height:auto');
  });
  $('#search-submit').click(function(){
    $("#smpush-noheader-value").remove();
  });
  $('#post-query-submit').click(function(event){
    $("#smpush-noheader-value").remove();
  });
  $('.smpush-applytoall').click(function(event){
    if(!confirm("Action will be applied to all results, continue?")){
      event.preventDefault();
      return;
    }
  });
  $('.smio-delete').click(function(event){
    var confirmtxt = $(this).attr("data-confirm");
    if(typeof confirmtxt == "undefined"){
      confirmtxt = "Are you sure you want to continue?";
    }
    if (!confirm(confirmtxt)){
      event.preventDefault();
    }
  });
  $('#smpush-clear-hisbtn').click(function(){
    var options = {
    url:           $('#smpush_histform').attr("action")+'&clearhistory=1&noheader=1',
    beforeSubmit:  function(){$('.smpush_process').show()},
    success:       function(responseText, statusText){if(responseText!=1){console.log(responseText);}else{$('.smpush_process').hide();}}
    };
    $('#smpush_histform').ajaxSubmit(options);
  });
  $('#smpush-save-hisbtn').click(function(){
    var options = {
    url:           $('#smpush_histform').attr("action")+'&savehistory=1&noheader=1',
    beforeSubmit:  function(){$('.smpush_process').show()},
    success:       function(responseText, statusText){if(responseText!=1){console.log(responseText);}else{$('.smpush_process').hide();}}
    };
    $('#smpush_histform').ajaxSubmit(options);
  });
  $('.smpush-payload').change(function(){
   if($(this).val() == "multi"){
     $(".smpush-payload-normal").hide();
     $(".smpush-payload-multi").show();
   }
   else{
     $(".smpush-payload-multi").hide();
     $(".smpush-payload-normal").show();
   }
  });
  $('.and_smpush-payload').change(function(){
   if($(this).val() == "multi"){
     $(".and_smpush-payload-normal").hide();
     $(".and_smpush-payload-multi").show();
   }
   else{
     $(".and_smpush-payload-multi").hide();
     $(".and_smpush-payload-normal").show();
   }
  });
});

function smpush_delete_service(id){
  if(!confirm("Are you sure you want to continue?")){
    return;
  }
  $('.smpush_service_'+id+'_loading').show();
  $.get(smpush_pageurl, {'noheader':1, 'delete': 1, 'id': id}
  ,function(data){
    $('.smpush_service_'+id+'_loading').hide();
    $('#smpush-service-tab-'+id).hide(600, function() {
      $('#smpush-service-tab-'+id).remove("push-alternate");
    });
  });
}

function smpush_open_service(id, actiontype, action){
  if(actiontype == 1){
    if(confirm("Do you want to save current changes?")){
      $('#smpush_jform').ajaxSubmit();
    }
  }
  else if(actiontype == 2){
    $(".smpush-canhide").hide();
    $("#col-left").attr("style", "width:55%");
  }
  $(".smpush_form_ajax").show();
  $('.smpush-service-tab').removeClass("push-alternate");
  $('#smpush-service-tab-'+id).addClass("push-alternate");
  $('.smpush_service_'+id+'_loading').show();
  $.get(smpush_pageurl, {'noheader':1, 'action': action, 'id': id}
  ,function(data){
    $('.smpush_form_ajax').html(data);
    var smpush_form_options = {
        beforeSubmit:  function(){$('.smpush_process').show()},
        success:       function(responseText, statusText){
          if(responseText != 1){
            $(".smpush_process").hide();
            alert(responseText['message']);
          }
          else{
            $(".smpush_process").hide();
            $(".smpush_form_ajax").fadeOut("fast", function(){
              $('.smpush_form_ajax').html('');
              if(actiontype == 2){
                $("#col-left").attr("style", "width:100%");
                $(".smpush-canhide").show();
              }
              if(id != -1){
                $("html, body").animate({scrollTop: $('#smpush-service-tab-'+id).offset().top-100}, "slow");
              }
            });
          }
        }
    };
    $('#smpush_jform').ajaxForm(smpush_form_options);
    $('#smio-submit').click(function(){
      var form = $(this).parents('form');
      if (!validateForm(form)) return false;
    });
    $('.smpush_service_'+id+'_loading').hide();
    if(id != -1)$("html, body").animate({scrollTop: 0}, "slow");
  });
}

function SMPUSH_ProccessQueue(baseurl, allcount, increration){
  if(allcount == 0){
    $("#smpush_progressinfo").append("<p class='error'>There is no tokens accept your choices</p>");
    return;
  }
  if(smpush_pro_currcount == 0){
    $("#smpush_progressinfo").append("<p>Start queuing "+allcount+" token in the queue table</p>");
  }

  $.getJSON(baseurl+'admin.php?page=smpush_send_notification', {'noheader':1, 'lastid':smpush_lastid, 'increration':increration}
  ,function(data){
    if(typeof(data) === "undefined" || data === null){
      $("#smpush_progressinfo").append("<p class='error'>Escaped and try reconnect...</p>");
      smpush_resum_timer = setTimeout(function(){SMPUSH_ProccessQueue(baseurl, allcount, increration)}, 2000);
      return;
    }

    if(data.respond != 0){
      smpush_pro_currcount = smpush_pro_currcount+increration;
      smpush_pro_percent = Math.floor(((smpush_pro_currcount)/allcount)*100);
      $("#smpush_progressbar").progressbar("value", smpush_pro_percent);
      $(".smpush_progress_label").text(smpush_pro_percent+'%');
    }

    if(data.respond == 1){
      smpush_lastid = data.message;
      SMPUSH_ProccessQueue(baseurl, allcount, increration);
    }
    else if(data.respond == -1){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text('Complete');
      $("#smpush_progressinfo").append('<p>Queuing completed and start now in sending process</p>');
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == -2){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text('Complete');
      $("#smpush_progressinfo").append('<p>Queuing completed for cron scheduled sending process</p>');
    }
    else if(data.respond == 0) $("#smpush_progressinfo").append(data.message);
    else $("#smpush_progressinfo").append('<p class="error">Error ocurred refresh the page</p>');
  }).fail(function(error) {
    console.log(error.responseText);
    $("#smpush_progressinfo").append("<p class='error'>Escaped and try reconnect...</p>");
    smpush_resum_timer = setTimeout(function(){SMPUSH_ProccessQueue(baseurl, allcount, increration)}, 2000);
  });
}

function SMPUSH_RunQueue(baseurl, allcount){
  $.getJSON(baseurl+'admin.php?page=smpush_runqueue', {'noheader':1, 'getcount':0, 'firstrun':smpush_firstrun, 'google_notify':smpush_google_open, 'feedback_open':smpush_feedback_open, 'feedback_google':smpush_feedback_google}
  ,function(data){
    smpush_firstrun = 0;
    if(typeof(data) === "undefined" || data === null){
      $("#smpush_progressinfo").append("<p class='error'>Escaped and try reconnect...</p>");
      smpush_resum_timer = setTimeout(function(){SMPUSH_RunQueue(baseurl, allcount)}, 3000);
      return;
    }

    if(data.respond != 0){
      if(allcount == -1){
        $(".smpush_progress_label").text("Start feedback service");
      }
      else{
        smpush_percent = Math.floor((smpush_currcount/allcount)*100);
        $("#smpush_progressbar").progressbar("value", smpush_percent);
        $(".smpush_progress_label").text(smpush_percent+'%');
        if(smpush_percent >= 100){
          $(".smpush_progress_label").text("Start feedback service");
        }
      }
    }

    if(data.respond == 1){
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == -1){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text('Complete');
      $("#smpush_progressinfo").append(data.message);
      $("#smpush_progressinfo").append('<p>Complete...</p>');
      $("#cancel_push").val('Exit and return back');
    }
    else if(data.respond == 2){
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      if(data.result.message != ""){
        $("#smpush_progressinfo").append(data.result.message);
      }
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 3){
      smpush_google_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 4){
      smpush_feedback_open = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 5){
      smpush_feedback_google = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 0) $("#smpush_progressinfo").append(data.message);
    else $("#smpush_progressinfo").append('<p class="error">Error ocurred refresh the page</p>');
  }).fail(function(error) {
    console.log(error.responseText);
    $("#smpush_progressinfo").append("<p class='error'>Escaped and try reconnect...</p>");
    smpush_resum_timer = setTimeout(function(){SMPUSH_RunQueue(baseurl, allcount)}, 3000);
  });
}
