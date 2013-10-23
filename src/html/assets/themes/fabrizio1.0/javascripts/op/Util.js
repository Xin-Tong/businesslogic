(function($) {
  if(typeof TBX === 'undefined')
    TBX = {};

  function Util() {
    var enableBetaFeatures = false, currentPage = null, fetchCache = {};

    this.currentPage = function(/*[set]*/) {
      if(arguments.length === 1)
        currentPage = arguments[0];
      return currentPage;
    };

    this.enableBetaFeatures = function(/*[set]*/) {
      if(arguments.length === 1)
        enableBetaFeatures = arguments[0];
      return enableBetaFeatures;
    };

    this.fetchAndCache = function(src) {
      if(typeof(fetchCache[src]) === 'undefined')
        $('<img />').attr('src', src).appendTo('body').css('display', 'none').on('load', function(ev) { $(ev.target).remove(); });
      fetchCache[src] = 1;
    };

    // http://stackoverflow.com/questions/12662824/twitter-bootstrap-typeahead-multiple-values
    // used by typeahead plugin
    this.tagExtractor = function(query) {
      var result = /([^,]+)$/.exec(query);
      if(result && result[1])
          return result[1].trim();
      return '';
    };

    this.getPathParam = function(name) {
      var re = new RegExp(TBX.format.sprintf('/%s-([^/]+)/', name)), result = re.exec(location.pathname);
      if(result.length === 2)
        return result[1];
      return null;
    };

    this.getQueryParam = function(name) {
      var re = new RegExp(TBX.format.sprintf('%s=([^&]+)', name)), result = re.exec(location.search);
      if(result.length === 2)
        return result[1];
      return null;
    };
  }

  TBX.util = new Util;
})(jQuery);
