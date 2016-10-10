function FilterDueDate() {
	return function(lists) {
		var filtered = [];
		var duedate = [];

		// for some reason the sort feature isn't working for this due date
		angular.forEach(lists, function(l, k) {
			if (typeof l.launchdate === 'undefined' || parseFloat(l.launchdate) == 0 || l.launchdate == "") {
				// we keep this so that we can push it at the end
				//console.log('pushin', l);
				//filtered = filtered.concat([l]);
				//var random = Math.random() * (9999999999 - 9991111111) + 9991111111;
				var random = '9999999999';
				lists[k].launchdate = random;
				//lists.splice(k,1);
			} else {
				//lists[k].launchdate = parseFloat(l.launchdate);
			}
		});

		/*for(var i = lists.length -1; i >= 0 ; i--){
			var l = lists[i];
			if(typeof l.launchdate === 'undefined' || parseFloat(l.launchdate) == 0 || l.launchdate == ""){
				lists.splice(i, 1);
			} else {
				lists[i].launchdate = parseFloat(l.launchdate);
			}
		}*/
		/*lists.sort(function(a,b) {
			return parseFloat(a.launchdate) - parseFloat(b.launchdate);
		});

		// let's make sure there are no duplicates
		var dupes = [];
		for(var i = lists.length -1; i >= 0 ; i--){
			var l = lists[i];
			console.log(l.)
			if (dupes.indexOf(l.id) > -1) {
				console.log('dupe', dupes.indexOf(l.id), l.id, dupes);
				lists.splice(i, 1);
			} else {
				dupes.push(l.id);
			}
		}*/

		//console.log('duedates', filtered);

		return lists;
	}
}