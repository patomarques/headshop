/**
 * Storefront Child Theme JavaScript
 * 
 * @package Storefront_Child
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Aguardar o documento estar pronto
    $(document).ready(function() {
        
        // Inicializar funcionalidades
        initSmoothScrolling();
        initProductHover();
        initCartUpdates();
        initSearchEnhancements();
        initMobileMenu();
        initLazyLoading();
        initCustomSearch();
        
    });

    /**
     * Scroll suave para links âncora
     */
    function initSmoothScrolling() {
        $('a[href*="#"]:not([href="#"])').click(function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                    return false;
                }
            }
        });
    }

    /**
     * Efeitos de hover nos produtos
     */
    function initProductHover() {
        $('.woocommerce ul.products li.product').hover(
            function() {
                $(this).addClass('product-hover');
                $(this).find('.woocommerce-loop-product__title').addClass('title-hover');
            },
            function() {
                $(this).removeClass('product-hover');
                $(this).find('.woocommerce-loop-product__title').removeClass('title-hover');
            }
        );
    }

    /**
     * Atualizações do carrinho via AJAX
     */
    function initCartUpdates() {
        // Atualizar carrinho quando quantidade for alterada
        $(document).on('change', '.woocommerce-cart-form .qty', function() {
            var $form = $(this).closest('form');
            var $button = $form.find('.update-cart-button');
            
            if ($button.length) {
                $button.prop('disabled', true).text(storefront_child_ajax.loading_text);
                
                // Simular atualização (em um caso real, você faria uma requisição AJAX)
                setTimeout(function() {
                    $button.prop('disabled', false).text('Atualizar Carrinho');
                    showNotification('Carrinho atualizado!', 'success');
                }, 1000);
            }
        });

        // Adicionar produto ao carrinho
        $(document).on('click', '.single_add_to_cart_button', function(e) {
            var $button = $(this);
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(storefront_child_ajax.loading_text);
            
            // Simular adição ao carrinho
            setTimeout(function() {
                $button.prop('disabled', false).text(originalText);
                showNotification('Produto adicionado ao carrinho!', 'success');
            }, 1500);
        });
    }

    /**
     * Melhorias na busca
     */
    function initSearchEnhancements() {
        var $searchForm = $('.woocommerce-product-search-form');
        var $searchInput = $searchForm.find('input[type="search"]');
        
        if ($searchInput.length) {
            // Adicionar placeholder dinâmico
            var placeholders = [
                'Buscar produtos...',
                'Digite o nome do produto...',
                'O que você está procurando?'
            ];
            
            var currentPlaceholder = 0;
            setInterval(function() {
                $searchInput.attr('placeholder', placeholders[currentPlaceholder]);
                currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
            }, 3000);

            // Busca em tempo real (simulada)
            $searchInput.on('input', function() {
                var query = $(this).val();
                if (query.length >= 3) {
                    // Aqui você poderia implementar busca em tempo real
                    console.log('Buscando por:', query);
                }
            });
        }
    }

    /**
     * Menu mobile melhorado
     */
    function initMobileMenu() {
        var $mobileToggle = $('.menu-toggle');
        var $mobileMenu = $('.main-navigation');
        
        if ($mobileToggle.length && $mobileMenu.length) {
            $mobileToggle.on('click', function(e) {
                e.preventDefault();
                $mobileMenu.slideToggle(300);
                $(this).toggleClass('active');
            });

            // Fechar menu ao clicar em um link
            $mobileMenu.find('a').on('click', function() {
                if ($(window).width() <= 768) {
                    $mobileMenu.slideUp(300);
                    $mobileToggle.removeClass('active');
                }
            });
        }
    }

    /**
     * Lazy loading para imagens
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            $('img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }
    }

    /**
     * Mostrar notificações
     */
    function showNotification(message, type) {
        type = type || 'info';
        
        var $notification = $('<div class="custom-notification notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        // Animar entrada
        $notification.fadeIn(300);
        
        // Remover após 3 segundos
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Adicionar efeitos de scroll
     */
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        
        // Header fixo com efeito
        if (scrollTop > 100) {
            $('.site-header').addClass('header-scrolled');
        } else {
            $('.site-header').removeClass('header-scrolled');
        }
        
        // Animação de elementos ao entrar na viewport
        $('.animate-on-scroll').each(function() {
            var elementTop = $(this).offset().top;
            var elementBottom = elementTop + $(this).outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('animated');
            }
        });
    });

    /**
     * Melhorar acessibilidade
     */
    function initAccessibility() {
        // Adicionar skip links
        if (!$('.skip-link').length) {
            $('body').prepend('<a class="skip-link screen-reader-text" href="#main">Pular para o conteúdo</a>');
        }
        
        // Melhorar navegação por teclado
        $('.menu-toggle').on('keydown', function(e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter ou Space
                e.preventDefault();
                $(this).click();
            }
        });
        
        // Adicionar ARIA labels
        $('.woocommerce-product-search-form input[type="search"]').attr('aria-label', 'Buscar produtos');
        $('.menu-toggle').attr('aria-label', 'Alternar menu de navegação');
    }

    // Inicializar acessibilidade
    initAccessibility();

    /**
     * Otimizações de performance
     */
    function initPerformanceOptimizations() {
        // Debounce para eventos de scroll
        var scrollTimeout;
        $(window).on('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                // Código de scroll aqui
            }, 10);
        });
        
        // Throttle para eventos de resize
        var resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Código de resize aqui
            }, 250);
        });
    }

    // Inicializar otimizações
    initPerformanceOptimizations();

    /**
     * Funcionalidades específicas do WooCommerce
     */
    function initWooCommerceFeatures() {
        // Melhorar galeria de produtos
        if ($('.woocommerce-product-gallery').length) {
            $('.woocommerce-product-gallery__image').on('click', function() {
                var $img = $(this).find('img');
                if ($img.length) {
                    // Abrir lightbox customizado
                    openLightbox($img.attr('src'), $img.attr('alt'));
                }
            });
        }
        
        // Adicionar contador de caracteres para reviews
        $('.comment-form-comment textarea').on('input', function() {
            var maxLength = 500;
            var currentLength = $(this).val().length;
            var $counter = $(this).siblings('.char-counter');
            
            if (!$counter.length) {
                $counter = $('<div class="char-counter"></div>');
                $(this).after($counter);
            }
            
            $counter.text(currentLength + '/' + maxLength + ' caracteres');
            
            if (currentLength > maxLength) {
                $counter.addClass('over-limit');
            } else {
                $counter.removeClass('over-limit');
            }
        });
    }

    // Inicializar funcionalidades do WooCommerce
    initWooCommerceFeatures();

    /**
     * Inicializar busca customizada com ícone
     */
    function initCustomSearch() {
        var $searchToggle = $('.custom-search .search-icon');
        var $searchContainer = $('.custom-search .search-form-container');
        var $searchField = $('.custom-search .search-field');
        var $searchClose = $('.custom-search .search-close');
        var $body = $('body');

        // Abrir busca ao clicar no ícone
        $searchToggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            $searchContainer.addClass('show').show();
            $searchField.focus();
            $body.addClass('search-open');
        });

        // Fechar busca ao clicar no botão fechar
        $searchClose.on('click', function(e) {
            e.preventDefault();
            closeSearch();
        });

        // Fechar busca ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.custom-search').length) {
                closeSearch();
            }
        });

        // Fechar busca com ESC
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC
                closeSearch();
            }
        });

        // Função para fechar busca
        function closeSearch() {
            $searchContainer.removeClass('show').hide();
            $body.removeClass('search-open');
            $searchField.val('');
        }

        // Melhorar acessibilidade
        $searchToggle.on('keydown', function(e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter ou Space
                e.preventDefault();
                $(this).click();
            }
        });

        $searchClose.on('keydown', function(e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter ou Space
                e.preventDefault();
                $(this).click();
            }
        });

        // Auto-focus no campo de busca quando aberto
        $searchField.on('focus', function() {
            $(this).select();
        });

        // Busca em tempo real (opcional)
        var searchTimeout;
        $searchField.on('input', function() {
            var query = $(this).val();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(function() {
                    // Aqui você pode implementar busca em tempo real
                    console.log('Buscando por:', query);
                }, 300);
            }
        });

        // Melhorar UX em mobile
        if ($(window).width() <= 768) {
            $searchContainer.on('touchstart', function(e) {
                e.stopPropagation();
            });
        }
    }

    /**
     * Abrir lightbox customizado
     */
    function openLightbox(src, alt) {
        var $lightbox = $('<div class="custom-lightbox"><div class="lightbox-content"><img src="' + src + '" alt="' + alt + '"><button class="lightbox-close">&times;</button></div></div>');
        
        $('body').append($lightbox);
        $lightbox.fadeIn(300);
        
        // Fechar lightbox
        $lightbox.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('lightbox-close')) {
                $lightbox.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
        
        // Fechar com ESC
        $(document).on('keydown.lightbox', function(e) {
            if (e.keyCode === 27) { // ESC
                $lightbox.fadeOut(300, function() {
                    $(this).remove();
                });
                $(document).off('keydown.lightbox');
            }
        });
    }

})(jQuery);
