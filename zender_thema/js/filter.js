  document.addEventListener('DOMContentLoaded', function () {
    const tv2goRadio = document.getElementById('check-TV2GO');
    const dvbcRadio = document.getElementById('check-DVB-C'); // voor reset
    const basisWrapper = document.getElementById('basispakket-wrapper');
    const radioWrapper = document.getElementById('radio-pakketten-wrapper');

    function updateVisibility() {
      if (tv2goRadio.checked) {
        basisWrapper.style.display = 'none';
        radioWrapper.style.display = 'none';
      } else {
        basisWrapper.style.display = '';
        radioWrapper.style.display = '';
      }
    }

    // Initial check
    updateVisibility();

    // Event listeners
    tv2goRadio.addEventListener('change', updateVisibility);
    dvbcRadio.addEventListener('change', updateVisibility);
  });