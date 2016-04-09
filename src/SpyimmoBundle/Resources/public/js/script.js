$(function () {

    $.extend( true, $.fn.dataTable.defaults, {
        "searching": true
    } );

	 $('#offerTable').DataTable({
		"lengthMenu": [[25, 50, -1], [25, 50, "All"]],
	 	"order": [[ 7, "desc" ]],
        "language": {
             "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
        }
	 });


    $(document).on("click", ".offerLink", function () {
        console.log('get');
        $.ajax({
            url: Routing.generate('detail', {id: $(this).closest('tr').data('id')})
        }).done(function (html) {
            $('.modal-content').html(html);
        });
    });


    $(document).on("click", ".favorite", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('favorite', {id: $(this).data('id')})
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
            url: Routing.generate('unfavorite', {id: $(this).data('id')})
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
            url: Routing.generate('hide', {id: $(this).data('id')})
        }).done(function () {
            button.parent().parent().hide('slow');
        });
    });

    $(document).on("click", ".unhideAction", function () {
        var button = $(this);
        $.ajax({
            url: Routing.generate('unhide', {id: $(this).data('id')})
        }).done(function () {
            button.parent().parent().hide('slow');
        });
    });

    $(document).on("click", ".contactedAction", function () {
        var button = $(this);
        if (confirm("Are you sure to mark this offer as contacted ?")) {
            $.ajax({
                url: Routing.generate('contacted', {id: $(this).data('id')})
            }).done(function () {
                button.parent().parent().find('.glyphicon-envelope').show();
                button.hide();
            });
        }
    });


});