$(function(){
  var startup, saveForm, loadForm, messageBox, conn, hash;
  var port = "443";
  var saved = true;

  $(window).bind("beforeunload", function(ev) {
    if (!saved) {
      ev.returnValue = "保存されていないコードは破棄されます．";
      return "保存されていないコードは破棄されます．";
    }
  });

  var init = function() {
    startup = $('[data-remodal-id=startup]').remodal({
      hashTracking: false,
      closeOnAnyClick: false,
      closeOnEscape: false
    });

    saveForm =  $('[data-remodal-id=save]').remodal({
      hashTracking: false,
      closeOnAnyClick: false,
      closeOnEscape: false,
      closeOnConfirm: false
    });

    $('button[type=submit]', saveForm.$modal).css({display: "none"});
    $('.remodal-confirm', saveForm.$modal).bind('click', function(){
      $('button[type=submit]', saveForm.$modal).click();
    });
    $('form', saveForm.$modal).bind('submit', function(e) {
      e.preventDefault();
      saveCode();
      return false;
    });

    messageBox =  $('[data-remodal-id=message]').remodal({
      hashTracking: false,
      closeOnAnyClick: false,
      closeOnEscape: false
    });

    loadForm =  $('[data-remodal-id=load]').remodal({
      hashTracking: false,
      closeOnAnyClick: false,
      closeOnEscape: false,
      closeOnConfirm: false
    });
    $('button[type=submit]', loadForm.$modal).css({display: "none"});
    $('.remodal-confirm', loadForm.$modal).bind('click', function(){
      $('button[type=submit]', loadForm.$modal).click();
    });
    $('form', loadForm.$modal).bind('submit', function(e) {
      e.preventDefault();
      loadCode();
      return false;
    });

    if (id === "") {
      $.ajax({
        type: "GET",
        url: "/api/id",
        dataType: "json",
      }).done(function(data){
        $('[data-remodal-id=startup] .qrcode')
          .bind('load', function(){
            startup.open();
          })
          .attr({src: "/api/qr/"+data.qr_id+"?mode=debug"});

        id = data.qr_id;
        hash = data.hash;
        history.replaceState(null, null, location.pathname + id);
        connectServer();
        resizePreview();
      });
    } else {
      $.ajax({
        type: "POST",
        url: '/api/sql',
        data: JSON.stringify({id: id, mode: "exist"})
      }).done(function(data) {
        loadForm.open();
      }).fail(function(data){
        history.replaceState(null, null, "/editor");
        location.reload();
      });
    }
  };

  var saveCode = function() {
    var form = $('form', saveForm.$modal);
    var param = {
      mode: "save",
      published: ($('[name=published]', saveForm.$modal)[0].checked) ? 1 : 0,
      code: $('.editor').val(),
      id: id,
      hash: hash
    };
    $(form.serializeArray()).each(function(i, v) {
      if (param[v.name] == null) param[v.name] = v.value;
    });
    $.ajax({
      type: "POST",
      url: '/api/sql',
      data: JSON.stringify(param)
    }).done(function(data) {
      if (data.status === "success") {
        saved = true;
        $('.title', messageBox.$modal).text("Saved!");
        $('.message', messageBox.$modal).empty();
        if ($('[name=published]', saveForm.$modal)[0].checked) {
          var msg = $('<p>').text("下のQRコードから実行ができます\n再編集には現在のURLが必要です");
          msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
          var strongMsg = $('<p>').append($("<strong>").text("現在のURL：" + location.href));
          var qrcode = $('<img>').attr({src: "/api/qr/"+id});
          var twitter = $('<a>').attr({
            href: "https://twitter.com/share",
            "data-url": location.origin + "/viewer/" + id,
            "data-text": $('[name=title]', saveForm.$modal).val() + " / " + $('[name=username]', saveForm.$modal).val(),
            "data-lang": "ja",
            "data-size": "large",
            "data-related": "processinga",
            "data-count": "none",
            "data-hashtags": "processinga"
          }).addClass("twitter-share-button").text("ツイート");

          $('.message', messageBox.$modal).append(msg).append(strongMsg)
            .append(qrcode).append("<br>")
            .append(twitter).append('<script src="//platform.twitter.com/widgets.js" id="twitter-wjs" async></script>');
        } else {
          var msg = $('<p>').text("再編集には現在のURLが必要です");
          msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
          var strongMsg = $('<p>').append($("<strong>").text("現在のURL：" + location.href));

          $('.message', messageBox.$modal).append(msg).append(strongMsg);
        }

        saveForm.close();
        messageBox.open();
      } else {
        var err = (data.message) ? data.message : "";
        $('.title', messageBox.$modal).text("Error");
        var msg = $('.message', messageBox.$modal).text("エラーが発生しました\n再度試してください\n" + err);
        msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
        messageBox.open();
      }
    }).fail(function(data) {
      var err = (data.responseJSON.message) ? data.responseJSON.message : "";
      $('.title', messageBox.$modal).text("Error");
      var msg = $('.message', messageBox.$modal).text("エラーが発生しました\n再度試してください\n" + err);
      msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
      messageBox.open();
    });
  };

  var loadCode = function() {
    var form = $('[data-remodal-id=load] > form');
    var param = {
      mode: "load",
      id: id
    };
    $(form.serializeArray()).each(function(i, v) {
      if (param[v.name] == null) param[v.name] = v.value;
    });
    $.ajax({
      type: "POST",
      url: '/api/sql',
      data: JSON.stringify(param)
    }).done(function(data) {
      if (data.status === "success") {
        loadForm.close();
        data = data.data; hash = data.hash;
        $('.editor').val(data.code);
        reloadSyntax($('.editor')[0]);
        $('[data-remodal-id=startup] .qrcode')
          .bind('load', function(){startup.open();})
          .attr({src: "/api/qr/"+data.id+"?mode=debug"});
        connectServer();
        resizePreview();
      } else {
        var err = (data.message) ? data.message : "";
        var msg = $('.error', loadForm.$modal).text("エラーが発生しました\n再度試してください\n" + err);
        msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
        msg.hide(); msg.fadeIn('slow');
      }
    }).fail(function(data) {
      var err = (data.message) ? data.message : "";
      var msg = $('.error', loadForm.$modal).text("エラーが発生しました\n再度試してください\n" + err);
      msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
      msg.hide(); msg.fadeIn('slow');
    });
  };

  var connectServer = function() {
    conn = new WebSocket('wss://' + location.hostname + ':' + port + '/chat');

    $(conn).bind('open', function(e) {
      e = e.originalEvent;
      console.log("Connection established!");
      if (id === "") id = "test";
      conn.send(JSON.stringify({
        id: id,
        action: 'join'
      }));
      conn.send(JSON.stringify({
        id: id,
        action: 'post',
        data: {
          type: 'request',
          message: 'info'
        }
      }));
    });

    $(conn).bind('message', function(e) {
      e = e.originalEvent;
      var json = JSON.parse(e.data);
      if (json.data == null) return;
      var data = json.data;
      console.log(data);
      if ($.inArray(data.type, ['log', 'error']) >= 0) {
        print[ data.type ](data.message);
      } else if (data.type === 'info' && data.message.isDevice === true) {
        var width = data.message.width; var height = data.message.height;
        $('option:contains("Connected Device")').attr({value: width + "," + height});
        $('i[title="Run with Device"]').attr({connected: 'true'});
        startup.close();
      } else if (data.type === 'users') {
        if (data.message <= 2) $('i[title="Run with Device"]').removeAttr('connected');
      }
    });

    $(conn).bind('error', function(e) {
      e = e.originalEvent;
      console.error("Connection Error.");
      conn = null;
    });
    $(conn).bind('close', function(e) {
      e = e.originalEvent;
      console.error("Connection Error.");
      conn = null;
    });
  };

  var printConsole = function(msg, type) {
    var msgDiv = $('<span>').text(msg);
    msgDiv.html(msgDiv.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
    if (type === "error") msgDiv.addClass('error');
    var logDiv = $('div.log').append(msgDiv);
    logDiv.scrollTop(logDiv[0].scrollHeight - logDiv.height());
  };

  var print = {
    console: printConsole,
    log: function(msg) {this.console(msg, 'log')},
    error: function(msg) {this.console(msg, 'error')},
  };

  var changeCursor = function(editor) {
    $('div.position').remove();
    var positionDiv = $('<div>').addClass('position');

    positionDiv.text($(editor).val().substring(0, editor.selectionStart));
    positionDiv.html(positionDiv.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));

    if (editor.selectionStart === editor.selectionEnd) {
      var cursor = $('<span>').addClass('cursor');
      positionDiv.append(cursor);
    } else {
      var selectionSpan = $('<span>').addClass('selection');
      selectionSpan.text($(editor).val().substring(editor.selectionStart, editor.selectionEnd));
      selectionSpan.html(selectionSpan.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
      positionDiv.append(selectionSpan);
    }

    var backStrSpan = $('<span>').text($(editor).val().substring(editor.selectionEnd) + "\u00A0");
    backStrSpan.html(backStrSpan.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
    positionDiv.append(backStrSpan);

    $(editor).parent().append(positionDiv);
    $('div.position')[0].scrollTop = editor.scrollTop;
  };

  var reloadSyntax = function(editor) {
    $('div.syntaxhighlighter').parent().remove();

    var pre = $('<pre>').addClass('brush: pde; gutter: false; toolbar: false;');
    var code = $(editor).val().replace(/^( *)/m, function(s){
      var space = '';
      for (var i=0; i<s.length; i++) space+='\u00A0';
      return space;
    }).replace(/^ *\n/g, '\u00A0\n');
    code += "\u00A0";
    pre.text(code);
    $(editor).parent().append(pre);
    SyntaxHighlighter.highlight();
    $('div.syntaxhighlighter')[0].scrollTop = editor.scrollTop;
  };

  var tabInsert = function(ev) {
    var elem = ev.target;
    var start = elem.selectionStart;
    var end = elem.selectionEnd;
    var value = elem.value;

    ev.keyCode = ev.keyCode || ev.which;

    if ($.inArray(ev.keyCode, [9,13,125]) >= 0) {
      if (ev.keyCode === 9) { /* Tab */
        if (!ev.shiftKey) {
          elem.value = "" + (value.substring(0, start)) + "  " + (value.substring(end));
          elem.selectionStart = elem.selectionEnd = start + 2;
        } else {
          var indent = value.substring(0, start).split(/\n/).reverse()[0].match(/^( *)$/)[0];
          if (indent != null && indent.length >= 2) {
            elem.value = "" + (value.substring(0, start - 2)) + (value.substring(end));
            elem.selectionStart = elem.selectionEnd = start - 2;
          } else return true;
        }
      } else if (ev.keyCode === 13) { /* Enter */
        var indent = value.substring(0, start).split(/\n/).reverse()[0].match(/^( *)(?:[^ ].*$|$)/)[1];
        if (value.substring(0, start).split(/\n/).reverse()[0].trim().substr(-1) === "{") {
          indent += "  ";
        }
        elem.value = "" + (value.substring(0, start)) + "\n" + indent + (value.substring(end));
        elem.selectionStart = elem.selectionEnd = start + indent.length + 1;
      } else if (ev.keyCode === 125) { /* } */
        if (!ev.shiftKey) return true;
        var indent = value.substring(0, start).split(/\n/).reverse()[0].match(/^( *)$/);
        if (indent == null || indent.length <= 0) return true;
        else indent = indent[0];
        if (indent != null && indent.length >= 2) {
          elem.value = "" + (value.substring(0, start - 2)) + "}" + (value.substring(end));
          elem.selectionStart = elem.selectionEnd = start - 2 + 1;
        } else return true;
      }
      reloadSyntax(elem);

      ev.preventDefault();
      return false;
    }
    return true;
  };

  var editorKeyBind = function(ev) {
    var elem = ev.target;
    var start = elem.selectionStart;
    var end = elem.selectionEnd;
    var value = elem.value;

    ev.keyCode = ev.keyCode || ev.which;

    if ($.inArray(ev.keyCode, [81, 82, 83]) >= 0 && ev.ctrlKey) {
      if (ev.keyCode === 82) { /* R */
        if (ev.shiftKey) runDevice();
        else runPreview();
      } else if (ev.keyCode === 81) { /* Q */
        stopRunning();
      } else if (ev.keyCode === 83) { /* S */
        if ($('.editor').val().length <= 0) {
          $('.title', messageBox.$modal).text("Error");
          var msg = $('.message', messageBox.$modal).text("ソースコードが空です");
          msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
          messageBox.open();
        } else saveForm.open();
      }

      ev.keyCode = 0;
      ev.preventDefault();
      return false;
    }
    return true;
  };

  var runPreview = function() {
    if ($('.menu > *[running]').length > 0) return;
    $('.menu input, .menu select').attr({disabled: "true"});
    $('.menu > i[title=Run]').attr({running:'true'});
    $('.menu > i[title="Run with Device"]').attr({disabled: 'true'});
    $('div.log').empty();

    var frm = $('<form>').attr({
      action: '/debug',
      method: 'POST',
      target: 'preview'
    }).css({display: 'none'});
    var idInput = $('<input>').attr({name: 'id'})
      .css({display: 'none'}).val(id);
    var code = $("<textarea>").attr({name: 'code'})
      .css({display: 'none'}).val($('textarea.editor').val());
    frm.append(idInput).append(code);
    $('body').append(frm);

    frm.submit();
    frm.remove();
  };

  var runDevice = function() {
    if ($('.menu > *[running]').length > 0) return;
    if ($('i[title="Run with Device"]').attr('connected') !== 'true') {
      $('h1', startup.$modal).text("Debug with Device");
      $('.remodal-cancel', startup.$modal).text("閉じる");
      startup.open();
      return;
    }
    $('.menu input, .menu select').attr({disabled: "true"});
    $('.menu > i[title="Run with Device"]').attr({running:'true'});
    $('.menu > i[title=Run]').attr({disabled: 'true'});
    $('div.log').empty();

    conn.send(JSON.stringify({
      id: id,
      action: 'post',
      data: {
        type: 'code',
        message: $('textarea.editor').val()
      }
    }));
  };

  var stopRunning = function() {
    $('.menu input, .menu select').removeAttr("disabled");
    $('.menu > .run-button').removeAttr('running').removeAttr("disabled");
    resizePreview();
    $('iframe[name=preview]').attr({src: "/debug"});
    conn.send(JSON.stringify({
      id: id,
      action: 'post',
      data: {
        type: 'code',
        message: ""
      }
    }));
  };

  var resizePreview = function() {
    var size = $('.menu select[name=screen]').val().split(',');
    if (size.length === 2) {
      $('.menu input').attr({disabled: "true"});
      $('.menu input[name=width]').val(size[0]);
      $('.menu input[name=height]').val(size[1]);
    } else {
      $('.menu input').removeAttr("disabled");
    }
    var width = parseInt($('.menu input[name=width]').val());
    var height = parseInt($('.menu input[name=height]').val());
    var zoom = Math.min($('div#preview').width()/width, $('div#preview').height()/height, 1.0);
    $('iframe[name=preview]').css({
      width: width + "px",
      height: height + "px",
      transform: "translate(-50%, -50%) scale(" + zoom + ")"
    });
  };

  $('textarea.editor')
    .bind('focus', function(){
      reloadSyntax(this);
      $('span.cursor').show();
    })
    .bind('input', function(){reloadSyntax(this);  saved = false;})
    .bind('click', function(){changeCursor(this);})
    .bind('keydown', function(){changeCursor(this);})
    .bind('keyup', function(){changeCursor(this);})
    .bind('keydown', function(ev){return tabInsert(ev);})
    .bind('keypress', function(ev){return tabInsert(ev);})
    .bind('scroll', function(){
      $('div.syntaxhighlighter')[0].scrollTop = this.scrollTop;
      $('div.position')[0].scrollTop = this.scrollTop;
    })
    .bind('mousedown', function(){
      this.selecting = true;
    })
    .bind('mousemove', function(){
      if (this.selecting) changeCursor(this);
    })
    .bind('mouseup', function(){
      this.selecting = false;
    })
    .bind('blur', function(){
      $('span.cursor').hide();
    });

  $(document).bind('keydown', function(ev){return editorKeyBind(ev);});

  $('i[title=Run]').bind('click', runPreview);
  $('i[title="Run with Device"]').bind('click', runDevice);
  $('i[title=Stop]').bind('click', stopRunning);
  $('i[title=Save]').bind('click', function(){
    if ($('.editor').val().length <= 0) {
      $('.title', messageBox.$modal).text("Error");
      var msg = $('.message', messageBox.$modal).text("ソースコードが空です");
      msg.html(msg.html().replace(/\n/g, '<br>').replace(/ /g, '&nbsp;'));
      messageBox.open();
      return;
    }
    saveForm.open();
  });
  $('i[title=QR]').bind('click',function(){
    $('h1', startup.$modal).text("Debug with Device");
    $('.remodal-cancel', startup.$modal).text("閉じる");
    startup.open();
  });

  $('select[name=screen]').bind('change', resizePreview);
  $('.menu > input').bind('change', resizePreview);

  $(window).bind('resize', resizePreview);
  init();
});
