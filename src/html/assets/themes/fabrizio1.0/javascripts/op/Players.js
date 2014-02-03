(function($) {
  if(typeof TBX === 'undefined')
    TBX = {};
  
  function Players() {
    var players = [], $scriptEl = $('.flow-player-script'), swfPath = $scriptEl.attr('data-swf'), html5 = $scriptEl.attr('data-html5'), keys = $scriptEl.attr('data-key');

    flowplayer.conf = {
      adaptiveRatio: true,
      splash: true,
      swf: swfPath,
      logo: '/assets/themes/fabrizio1.0/images/logo-white.png',
      key: keys
    };

    this.load = function(id, params) {
      var player, elementId = 'video-element-'+id, $lightbox = $('.op-lightbox'), $el;
      if($lightbox.length > 0)
        $el = $('.'+elementId, $lightbox);
      else
        $el = $('body>.container').find('.'+elementId);

      if(typeof(flowplayer.conf.key) !== 'undefined') {
        $el.attr('data-key', flowplayer.conf.key).addClass('no-volume').css('background-color', '#000'); 
      }

      $el.flowplayer({
        engine:'html5',
        // one video: a one-member playlist
        playlist: [
          [
             { mp4:  params.file }
          ]
        ],
        // TODO check if this ever works
        width:params.width,
        height:params.height
      })
    };
  }
  
  TBX.players = new Players;
})(jQuery);

