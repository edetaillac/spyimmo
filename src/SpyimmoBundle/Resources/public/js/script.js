$(function () {

    $.extend( true, $.fn.dataTable.defaults, {
        "searching": true
    } );

    var lengthMenu = [[25, 50, -1], [25, 50, "All"]];
    var language = {"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"};

	 $('#offerTable').DataTable({
		"lengthMenu": lengthMenu,
	 	"order": [[ 8, "desc" ]],
        "language": language
	 });

    $('#visitTable').DataTable({
        "lengthMenu": lengthMenu,
        "order": [[ 2, "desc" ]],
        "language": language
    });

    $('#favoriteTable').DataTable({
        "lengthMenu": lengthMenu,
        "order": [[ 7, "desc" ]],
        "language": language
    });

    $(document).on("click", ".offerLink", function () {
        var link = $(this);
        $.ajax({
            url: Routing.generate('detail', {id: link.closest('tr').data('id')})
        }).done(function (html) {
            $('.modal-content').html(html);
            var closestTr = link.closest('tr');
            closestTr.removeClass('warning');
            closestTr.find('span.glyphicon-flag').remove();
        });
    });

    $(document).on("click", ".favorite", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('favorite', {id: button.data('id')})
        }).done(function () {
            button.parent().parent().addClass("success");
            button.html("<span class=\"glyphicon glyphicon-star-empty\" aria-hidden=\"true\"></span>");
            button.removeClass("btn-success");
            button.addClass("btn-default");
            button.addClass("unfavorite");
            button.removeClass("favorite");
        });
    });

    $(document).on("click", ".unfavorite", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('unfavorite', {id: button.data('id')})
        }).done(function () {
            button.parent().parent().removeClass("success");
            button.html("<span class=\"glyphicon glyphicon-star\" aria-hidden=\"true\"></span>");
            button.removeClass("btn-default");
            button.addClass("btn-success");
            button.addClass("favorite");
            button.removeClass("unfavorite");
        });
    });

    $(document).on("click", ".hideAction", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('hide', {id: button.data('id')})
        }).done(function () {
            button.parent().parent().hide('slow');
        });
    });

    $(document).on("click", ".unhideAction", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('unhide', {id: button.data('id')})
        }).done(function () {
            button.parent().parent().hide('slow');
        });
    });

    $(document).on("click", ".contactedAction", function () {
        var button = $(this);
        if (confirm("Are you sure to mark this offer as contacted ?")) {
            $.ajax({
                url: Routing.generate('contacted', {id: button.data('id')})
            }).done(function () {
                button.parent().parent().find('.glyphicon-envelope').show();
                button.hide();
            });
        }
    });

    $(document).on("click", "#offerNoteValid", function () {
        var button = $(this);
        var form = button.closest('form');
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize()
        }).done(function (html) {
            if(html == 'OK') {
                $('.noteFormSaved').show();
            }
        });
    });

    $(document).on("click", "#offerVisitValid", function () {
        var button = $(this);
        var form = button.closest('form');
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize()
        }).done(function (html) {
            if(html == 'OK') {
                $('.visitFormSaved').show();
            }
        });
    });

    $(document).on("click", ".setVisit", function () {
        $('.visitForm').toggle();
    });

    $(document).on("click", ".setNote", function () {
        $('.noteForm').toggle();
    });



});