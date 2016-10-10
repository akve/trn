function DirectiveVariableTemplate() {
	return {
		restrict: 'EA',
		replace: true,
		transclude: false,
		link: function(scope, element, attrs) {
			scope.getContentUrl = function() {
				if (typeof attrs.tpl === 'undefined' || attrs.tpl == "") return false;

				var template = attrs.tpl.replace(/[^A-Za-z]/g, '');
				//return '/templates/default/Panels/' + template + '.html?'+ new Date("April 3, 2016 12:13:11").getTime();
				return '/remote.php?w=getTemplate&tpl=' + template + '&ds='+ new Date("April 3, 2016 12:14:11").getTime();
			}
		},
		template: '<div class="variable-container" layout-padding ng-include="getContentUrl()"></div>'
	};
};

function DirectivePagination() {
	return {
		restrict: 'EA',
		scope: {
			items: "=",
		},
		templateUrl: templatepath+'/templates/Pagination.html?'+ new Date("April 3, 2016 12:14:11").getTime(),
		link: function(scope, elem, attrs) {
			//save the items
			scope.original = scope.items;
			//console.log('items length', scope.items.length);

			//set some defaults
			scope.pagelength = 20;
			scope.currentpage = 0;
			scope.Pages = [];

			//lets create max pages
			scope.maxpages = Math.ceil(scope.original.length/scope.pagelength);
			if (scope.maxpages < 1) {
				scope.maxpages = 1;
			}

			//let's set how many pages there are
			scope.pagination = function(items, currentpage, pagelength) {
				var start = currentpage * pagelength;
				var end = parseFloat(start + pagelength);
				if (start < 1) {
					start = 0;
				}

				items = items.slice(start, end);

				return items;
			}

			scope.changeItems = function()
			{
				//let's also set the display items
				var subset = scope.pagination(scope.original,scope.currentpage,scope.pagelength);
				
				scope.items = subset;
			}

			scope.SetPages = function() {
				scope.Pages = [];
				//we don't show all the pages, just a subset of what's current and visible
				var pagestart = 1;
				var pageend = scope.maxpages;

				//determine the subset based on what page is visible
				if (Math.abs(scope.currentpage - pagestart) > 2) {
					pagestart = scope.currentpage - 2;
					if (Math.abs(pageend - pagestart) < 6) {
						pagestart = pageend - 6;
					}
				}

				if (Math.abs(pageend - pagestart) > 6) {
					pageend = pagestart + 6;
				}

				for(var i = pagestart; i <= pageend; i++) {
					scope.Pages.push(i);
				}

				//let's also set the display items
				scope.changeItems();
			}
			scope.SetPages();

			scope.ChangePage = function(next)
			{
				if (next < 1) next = 1;
				if (next > scope.maxpages) next = scope.maxpages;
				next = parseFloat(next - 1);

				scope.currentpage = next;

				//we have to reset what pages are visible
				scope.SetPages();

				//let's also set the display items
				scope.changeItems();
			}

			scope.GetActive = function(page)
			{
				page = page - 1;
				if (page == scope.currentpage) {
					return 'active';
				}
			}
		}
	}
}