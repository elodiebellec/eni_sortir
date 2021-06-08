let citySelectElement;
window.onload = updateLocationOnCitySelection;

function clearOptions(selectElement) {
    let i, length = selectElement.options.length - 1;
    for(i = length; i >= 0; i--) {
        selectElement.remove(i);
    }
}
function updateLocationsOptions() {

    const locationSelector = document.getElementById('outing_location');
    const cityData = {"cityName": citySelectElement.value};

    clearOptions(locationSelector);

    fetch('ajax-cityData', {method: 'POST', body: JSON.stringify(cityData)})
        .then(response => response.json())
        .then(jsonLocations => {
            Object
                .keys(jsonLocations)
                .map(l => {
                    const optionElement = document.createElement('option');
                    optionElement.text = jsonLocations[l];
                    return optionElement;
                })
                .forEach(option => {
                    locationSelector.add(option);
                })

            //Remplir le code postal
        })
}
//Trouver l'élément html a remplir
function updateLocationOnCitySelection(){
    citySelectElement = document.getElementById('outing_city');
    updateLocationsOptions();
    citySelectElement.onchange = updateLocationsOptions;

}