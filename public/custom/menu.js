function getDataMenu(id) {
	let array = [];
	$.ajax({
		url: url_get_menu + '/' + id,
		method: 'GET',
		success: function (res) {

            if(res.data){
                let data = res.data
                let data_menu = []
                let level1 = []
                let level2 = []
                let level3 = []

                if (data.length > 0) {
                    data.sort(function (x, y) {
                        return x.urut_menu - y.urut_menu;
                    });

                    data.forEach(element => {
                        if (element.tingkatan_menu == '0') {
                            level1 = pushSubMenu(level1, element)
                        }

                        if (element.tingkatan_menu == '1') {
                            level2 = pushSubMenu(level2, element)
                        }

                        if (element.tingkatan_menu == '2') {
                            level3 = pushSubMenu(level3, element)
                        }
                    });

                    for (let x = 0; x < level1[0].length; x++) {
                        if (level2.hasOwnProperty(level1[0][x].id)) {
                            level1[0][x].submenu = level2[level1[0][x].id]
                            for (let y = 0; y < level1[0][x].submenu.length; y++) {
                                if (level3.hasOwnProperty(level1[0][x].submenu[y].id)) {
                                    level1[0][x].submenu[y].submenu = level3[level1[0][x].submenu[y].id]
                                }
                            }
                        }
                    }

                    dataMenu = level1[0]
                    createHtmlMenu()
                }
            }
		},
		error: function (res) {
			console.log(res)
		}
	})
}

function pushSubMenu(array, data) {
	let iarray = {
        id: data.id_menu,
        parent: data.kepala_menu,
        label: data.nama_menu,
        filename: data.alias_menu,
        submenu: [],
        icon: data.gambar_menu,
        urut: data.urut_menu,
        show: data.lihat_akses_menu
    }
	if (array.hasOwnProperty(data.kepala_menu)) {
		array[data.kepala_menu].push(iarray)
	} else {
		array[data.kepala_menu] = []
		array[data.kepala_menu].push(iarray)
	}

	return array;
}

function createHtmlMenu() {
    let homeUrl = window.location.href.split('#')[0]
	let html = '<li>'
				+ '<a href="'+homeUrl+'" class="change-me" onclick="homeUrl">'
				+ '<i class="glyphicon glyphicon-home"></i> Beranda</a>'
				+ '</li>'
	for (let i = 0; i < dataMenu.length; i++) {
		if (dataMenu[i].show == '1') {
			if (dataMenu[i].submenu.length == 0) {
				if (dataMenu[i].filename.includes("_detail") == false) {
					html += '<li>'
						+ '<a href="/' + dataMenu[i].filename + '" onclick="' + dataMenu[i].filename + '();" id="' + dataMenu[i].filename + '" class="change-me">'
						+ '<i class="glyphicon glyphicon-option-vertical"></i> ' + dataMenu[i].label + '</a>'
						+ '</li>'
				}
			} else {
				html += '<li class="treeview">'
					+ '<a href="#"><i class="glyphicon glyphicon-' + dataMenu[i].icon + '"></i> <span>' + dataMenu[i].label + '</span>'
					+ '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>'
					+ '<ul class="treeview-menu">'
				let level2 = dataMenu[i].submenu

				for (let j = 0; j < level2.length; j++) {
					if (level2[j].show == '1') {
						if (level2[j].submenu.length == 0) {
							if (level2[j].filename.includes("_detail") == false) {
								html += '<li>'
									+ '<a href="/' + level2[j].filename + '" onclick="' + level2[j].filename + '();" id="' + level2[j].filename + '" class="change-me">'
									+ '<i class="glyphicon glyphicon-option-vertical"></i> ' + level2[j].label + '</a>'
									+ '</li>'
							}
						} else {
							html += '<li class="treeview">'
								+ '<a href="#"><i class="glyphicon glyphicon-' + level2[j].icon + '"></i> <span>' + level2[j].label + '</span>'
								+ '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>'
								+ '<ul class="treeview-menu">'
							let level3 = level2[j].submenu
							for (let k = 0; k < level3.length; k++) {
								if (level3[k].show == '1') {
									if (level3[k].submenu.length == 0) {
										if (level3[k].filename.includes("_detail") == false) {
											html += '<li>'
												+ '<a href="/' + level3[k].filename + '" onclick="' + level3[k].filename + '();" id="' + level3[k].filename + '" class="change-me">'
												+ '<i class="glyphicon glyphicon-option-vertical"></i> ' + level3[k].label + '</a>'
												+ '</li>'
										}
									}
								}
							}
							html += '</ul></li>'
						}
					}

				}
				html += '</ul></li>'
			}
		}

	}

	$('#target-menu').html(html)
}
