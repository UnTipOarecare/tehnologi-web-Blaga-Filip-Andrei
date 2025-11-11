// scripts.js
document.addEventListener("DOMContentLoaded", function() {
  const sliders = document.querySelectorAll('.image-slider');

  sliders.forEach(slider => {
    const images = slider.querySelectorAll('img');
    const prev = slider.querySelector('.prev');
    const next = slider.querySelector('.next');
    let index = 0;

    // ascunde toate imaginile, arată prima
    const showImage = (i) => {
      images.forEach(img => img.style.display = 'none');
      images[i].style.display = 'block';
    };

    showImage(index);

    prev.addEventListener('click', () => {
      index = (index - 1 + images.length) % images.length;
      showImage(index);
    });

    next.addEventListener('click', () => {
      index = (index + 1) % images.length;
      showImage(index);
    });
  });
});
