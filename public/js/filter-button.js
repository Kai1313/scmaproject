var triggerFilter = true;

(function () {
    triggerButtonFilter();
})()
function formatRepoNormalSelection(repo) {
    return repo.text || repo.text;
}

function formatRepoNormal(repo) {
    if (repo.loading) {
        return repo.text;
    }
    // scrolling can be used
    var markup = $('<span  data-name=' + repo.name + ' value=' + repo.id + '>' + repo.text + '</span>');
    return markup;
}

function trigger_filter() {
    if (triggerFilter) {
        triggerFilter = false;
    } else {
        triggerFilter = true;
    }

    console.log(triggerFilter);
    triggerButtonFilter();
}

function triggerButtonFilter() {
    if (triggerFilter) {
        $('.filter-div').removeClass('hidden');
    } else {
        $('.filter-div').addClass('hidden');
    }
}
