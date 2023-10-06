(function( $, wp ){

    const $add     = $('.sift--score_actions--add');
    const $list    = $('.score_actions--ol');
    const tmpl_row = wp.template( 'score-action' );

    $add.on( 'click', function(e) {
        e.preventDefault();

        $( tmpl_row( { row_slug: 'new-' + new Date().getTime() + '-' + Math.random() } ) ).appendTo( $list ).hide().slideDown( 200 );
    });

    $list.on( 'click', '.sift-delete', function(e) {
        e.preventDefault();
        const $li = $(e.target).closest('li');
        $li.slideUp( 200, function() { $(this).remove(); } );
    });

    $list.on( 'click', '.sift-earlier', function(e) {
        e.preventDefault();
        const $li = $(e.target).closest('li');
        $li.insertBefore( $li.prev() );
    });

    $list.on( 'click', '.sift-later', function(e) {
        e.preventDefault();
        const $li = $(e.target).closest('li');
        $li.insertAfter( $li.next() );
    });

})( jQuery, wp );
