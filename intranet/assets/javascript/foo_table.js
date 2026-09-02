$(document).ready(function () {
  $('.footable').each(function (i) {
    $(this).footable({
      paginate: $(this).data('do-not-paginate') !== true
    })
  })
})
