document.addEventListener('DOMContentLoaded', function() {
	const slider = document.querySelector('.banner-slider');
	const slides = document.querySelectorAll('.banner-slide');
	const prev = document.querySelector('.banner-prev');
	const next = document.querySelector('.banner-next');
	const dotsContainer = document.querySelector('.banner-dots');

	if (!slider || slides.length === 0) return;

	let currentIndex = 0;
	const totalSlides = slides.length;

	for (let i = 0; i < totalSlides; i++) {
		const dot = document.createElement('button');
		dot.classList.add('banner-dot');
		dot.setAttribute('aria-label', 'Ir para slide ' + (i + 1));
		dot.addEventListener('click', function() {
			goToSlide(i);
		});
		dotsContainer.appendChild(dot);
	}

	const dots = document.querySelectorAll('.banner-dot');

	function updateSlider() {
		slider.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
		dots.forEach((dot, i) => {
			dot.classList.toggle('active', i === currentIndex);
		});
	}

	function goToSlide(index) {
		currentIndex = index;
		updateSlider();
	}

	function nextSlide() {
		currentIndex = (currentIndex + 1) % totalSlides;
		updateSlider();
	}

	function prevSlide() {
		currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
		updateSlider();
	}

	if (prev) prev.addEventListener('click', prevSlide);
	if (next) next.addEventListener('click', nextSlide);

	updateSlider();

	setInterval(nextSlide, 5000);
});
