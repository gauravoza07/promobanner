/**
 * PromoBanner JavaScript
 * Handles carousel functionality for multiple banners
 */

(function($) {
    'use strict';

    /**
     * Initialize carousel when DOM is ready
     */
    $(document).ready(function() {
        initPromoBannerCarousel();
    });

    /**
     * Initialize promotional banner carousel
     * Only runs if there are multiple banners
     */
    function initPromoBannerCarousel() {
        const $carousel = $('.promobanner-carousel');

        if ($carousel.length === 0) {
            return; // No carousel needed
        }

        const $slides = $carousel.find('.promobanner-slide');
        let currentIndex = 0;
        let intervalId = null;

        // Initialize first slide
        showSlide(currentIndex);

        // Start automatic slideshow
        startSlideshow();

        /**
         * Show specific slide
         * @param {number} index - Slide index to show
         */
        function showSlide(index) {
            // Hide all slides
            $slides.removeClass('active').hide();

            // Show current slide
            $slides.eq(index).addClass('active').show();
        }

        /**
         * Start automatic slideshow
         */
        function startSlideshow() {
            intervalId = setInterval(function() {
                currentIndex = (currentIndex + 1) % $slides.length;
                showSlide(currentIndex);
            }, 4000); // Change slide every 4 seconds
        }

        /**
         * Stop automatic slideshow
         */
        function stopSlideshow() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        // Pause on hover
        $carousel.on('mouseenter', function() {
            stopSlideshow();
        });

        // Resume on mouse leave
        $carousel.on('mouseleave', function() {
            startSlideshow();
        });

        // Optional: Add navigation dots
        if ($slides.length > 1) {
            addNavigationDots();
        }

        /**
         * Add navigation dots for manual control
         */
        function addNavigationDots() {
            const $dotsContainer = $('<div class="promobanner-dots"></div>');

            $slides.each(function(index) {
                const $dot = $('<div class="promobanner-dot"></div>');
                if (index === 0) {
                    $dot.addClass('active');
                }

                $dot.on('click', function() {
                    currentIndex = index;
                    showSlide(currentIndex);
                    updateDots();
                });

                $dotsContainer.append($dot);
            });

            $carousel.append($dotsContainer);

            /**
             * Update active dot
             */
            function updateDots() {
                $dotsContainer.find('.promobanner-dot').removeClass('active');
                $dotsContainer.find('.promobanner-dot').eq(currentIndex).addClass('active');
            }
        }
    }

})(jQuery);