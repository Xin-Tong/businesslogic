(function($) {
  if(typeof TBX === 'undefined')
    TBX = {};
  
  function Players() {
    var players = [], $scriptEl = $('.flow-player-script'), swf = $scriptEl.attr('data-swf'), html5 = $scriptEl.attr('data-html5'), key = $scriptEl.attr('data-key');

    flowplayer.conf = {
      splash: true,
      adaptiveRatio: true
    };

    this.load = function(id, params) {
      var player, elementId = 'video-element-'+id, $lightbox = $('.op-lightbox'), $el;
      if($lightbox.length > 0)
        $el = $('.'+elementId, $lightbox);
      else
        $el = $('body>.container').find('.'+elementId);

      $el.flowplayer({
        key: key,
        engine:'html5',
        src:swf,
        // one video: a one-member playlist
        playlist: [
          [
             { mp4:  params.file }
          ]
        ],
        // TODO check if this ever works
        width:params.width,
        height:params.height,
        canvas: {backgroundColor:'#000000'},
        plugins: {
          controls: {
            progressColor: '#000000',
            bufferColor: '#000000'
          }
        }
      })
    };
  }
  
  TBX.players = new Players;
})(jQuery);

