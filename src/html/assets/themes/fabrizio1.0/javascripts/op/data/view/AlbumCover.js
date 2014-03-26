(function($){
  op.ns('data.view').AlbumCover = op.data.view.Editable.extend({
    initialize: function() {
      this.model.on('change', this.modelChanged, this);
    },
    model: this.model,
    className: 'album-meta',
    template    :_.template($('#album-meta').html()),
    editable    : {
      '.name.edit' : {
        name: 'name',
        placement: 'top',
        title: 'Edit Album Name',
        validate : function(value){
          if($.trim(value) == ''){
            return 'Please enter a name';
          }
          return null;
        }
      }
    },
    events: {
      'click .delete': 'delete_',
      'click .share': 'share'
    },
    modelChanged: function() {
      this.render();
    },
    modelDestroyed: function() {
      var $el = $('.album-'+this.get('id'));
      $el.fadeTo('medium', .25);
    },
    delete_: function(ev) {
      ev.preventDefault();
      var $el = $(ev.currentTarget), id = $el.attr('data-id');
      OP.Util.makeRequest('/album/'+id+'/delete.json', {crumb: TBX.crumb()}, TBX.callbacks.albumDeleteForm, 'json', 'get');
    },
    share: function(ev) {
      ev.preventDefault();
      var $el = $(ev.currentTarget), id = $el.attr('data-id');
      OP.Util.makeRequest('/share/album/'+id+'/view.json', {crumb: TBX.crumb()}, TBX.callbacks.share, 'json', 'get');
    }
  });
})(jQuery);
