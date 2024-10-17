$(document).ready(function() {
    // Toggle search input
    $("#search-icon").click(function() {
        $(".nav").toggleClass("search");
        $(".nav").toggleClass("no-search");
        $(".search-input").toggleClass("search-active");
    });

    // Toggle mobile menu
    $('.menu-toggle').click(function() {
        $(".nav").toggleClass("mobile-nav");
        $(this).toggleClass("is-active");
        
        if ($(this).hasClass("is-active")) {
            $(this).attr("aria-expanded", "true");
        } else {
            $(this).attr("aria-expanded", "false");
        }
    });

    // Search functionality
    $(".search-input").on("input", function() {
        const searchValue = $(this).val().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        // Seleciona todos os elementos químicos pela classe "name"
        $(".label").each(function() {
            const elementName = $(this).find(".name").text().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const elementSymbol = $(this).find(".symbol").text().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // Verifica se o nome ou símbolo do elemento contém o valor de busca
            if (elementName.includes(searchValue) || elementSymbol.includes(searchValue)) {
                $(this).parent().show(); // Mostra o elemento pai
            } else {
                $(this).parent().hide(); // Oculta o elemento pai
            }
        });
    });
});