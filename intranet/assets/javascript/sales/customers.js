window.onload = function() {
    var getTree = function (element) {
        var tree = []

        $(element).children('li').each(function () {
            var $this = $(this)
            var $link = $this.children('a')
            var node = {
                text: $link.text().trim(),
                href: $link.attr('href'),
                selectable: false,
                state: {
                    expanded: true,
                    selected: $link.hasClass('customer-active')
                },

            }
            var $ul = $this.children('ul')
            if ($ul.length > 0 && $ul.children('li').length > 0) {
                node.tags = [$ul.find('li').length]
                node.nodes = getTree($ul)
            }
            tree.push(node)
        })
        return tree
    }

    $('#tree').treeview({
        data: getTree('#tree'),
        enableLinks: true,
        showTags: true,
        showBorder: false,
        selectedBackColor: '#23c6c8',
        collapseIcon: 'glyphicon glyphicon-chevron-up',
        expandIcon: 'glyphicon glyphicon-chevron-down'
    });
}