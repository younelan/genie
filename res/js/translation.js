// Fetch translations from the API and define a helper T()
(function(){
  // Define T immediately with fallback behavior
  window.T = function(key) {
    return window.translations && window.translations[key] ? 
      window.translations[key] : key;
  };

  const lang = window.userLang || 'en';
  fetch(`api/app.php?action=translations&lang=${lang}`)
    .then(res => res.json())
    .then(data => {
      window.translations = data.success ? data.translations : {};
    })
    .catch(err => {
      console.error('Translation fetch error', err);
      window.translations = {};
    });
})();
