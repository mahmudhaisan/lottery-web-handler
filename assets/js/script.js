jQuery(document).ready(function ($) {
    const sidebar = $('#custom-sidebar');
    const content = $('#custom-content');
    const cardContainer = $('#custom-card-container');
    const pagination = $('#custom-pagination');
    const filter = $('#custom-filter');
    const totalCards = 200;
    const cardsPerPage = parseInt(filter.val());

    $('#custom-toggle-sidebar').on('click', function() {
        sidebar.toggleClass('collapsed');
        content.toggleClass('collapsed');
    });

    function generateCards(page, perPage) {
        cardContainer.empty();
        const start = (page - 1) * perPage;
        const end = Math.min(start + perPage, totalCards);

        for (let i = start; i < end; i++) {
            cardContainer.append(`
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Card ${i + 1}</h5>
                            <p class="card-text">This is card number ${i + 1}.</p>
                        </div>
                    </div>
                </div>
            `);
        }
    }

    function generatePagination(total, perPage) {
        pagination.empty();
        const totalPages = Math.ceil(total / perPage);

        for (let i = 1; i <= totalPages; i++) {
            pagination.append(`
                <li class="page-item ${i === 1 ? 'active' : ''}">
                    <a class="page-link" href="#">${i}</a>
                </li>
            `);
        }

        pagination.find('.page-link').on('click', function(e) {
            e.preventDefault();
            pagination.find('.page-item').removeClass('active');
            $(this).parent().addClass('active');
            generateCards(parseInt($(this).text()), parseInt(filter.val()));
        });
    }

    filter.on('change', function() {
        const perPage = parseInt($(this).val());
        generateCards(1, perPage);
        generatePagination(totalCards, perPage);
    });

    generateCards(1, cardsPerPage);
    generatePagination(totalCards, cardsPerPage);
// Handle sidebar navigation



});



