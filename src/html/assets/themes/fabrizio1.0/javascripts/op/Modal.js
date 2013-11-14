(function($) {
  if(typeof TBX === 'undefined')
    TBX = {};
  
  function Modal() {
    var $modal = $('#modal');

    this.close = function() {
      $modal.modal('hide');
    };
    this.open = function(body) {
      $('.body-text', $modal).html(body);
      $modal.modal('show');
    };
  }
  TBX.modal = new Modal;
})(jQuery);

