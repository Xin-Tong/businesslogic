(function($) {
  if(typeof TBX === 'undefined')
    TBX = {};
  
  function Clipboard() {
    ZeroClipboard.setDefaults({
      forceHandCursor: true,
      moviePath: OP.Util.config.ZeroClipboardSWF,
      trustedOrigins: [window.location.protocol + "//" + window.location.host]
    });

    this.copy = function(tgt) {
      var $tgt = $(tgt), clip;
      clip = new ZeroClipboard(tgt);
      console.log('inited');

      clip.on( "load", function(client) {
        console.log('load');
        client.on( "complete", function(client) {
          $el = $(this);
          console.log('client complete');
          $el.find('i').remove();
          $('<i class="icon-ok"></i>').prependTo($el);
        });
      });
    };
  }
  
  TBX.clipboard = new Clipboard;
})(jQuery);


