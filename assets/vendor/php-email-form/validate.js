(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach(function (e) {
    e.addEventListener('submit', function (event) {
      event.preventDefault();

      let thisForm = this;
      let action = thisForm.getAttribute('action');
      let formData = new FormData(thisForm);

      if (!action) {
        displayError(thisForm, 'Form action URL is missing!');
        return;
      }

      // Show loading
      thisForm.querySelector('.loading').classList.remove('d-none');
      thisForm.querySelector('.error-message').classList.add('d-none');
      thisForm.querySelector('.success-message').classList.add('d-none');

      fetch(action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.text())
      .then(data => {
        thisForm.querySelector('.loading').classList.add('d-none');
        if (data.trim() === 'OK') {
          thisForm.querySelector('.success-message').classList.remove('d-none');
          thisForm.reset();
        } else {
          displayError(thisForm, data);
        }
      })
      .catch(error => {
        displayError(thisForm, 'An unexpected error occurred. Please try again later.');
        console.error('Form error:', error);
      });
    });
  });

  function displayError(thisForm, errorMsg) {
    thisForm.querySelector('.loading').classList.add('d-none');
    const errorDiv = thisForm.querySelector('.error-message');
    errorDiv.innerHTML = errorMsg;
    errorDiv.classList.remove('d-none');
  }
})();
