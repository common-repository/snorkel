(function() {
  function asyncLoad() {
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'https://disco-olark.herokuapp.com/widget.js?snorkel_api_key=' + snorkel_widget_config.snorkel_api_key;
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s, x);
  };
  if(window.attachEvent) {
    window.attachEvent('onload', asyncLoad);
  } else {
    window.addEventListener('load', asyncLoad, false);
  }
})();
