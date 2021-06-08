let citySelectElement;
let locationSelectElement;
let currentAjaxData;
let locationFields;
let postalCodeField;
window.onload = init;

function init() {
    /**
     * Once the page is loaded we capture all dom elements we'll need later.
     * @type {HTMLElement}
     */
    citySelectElement = document.getElementById('outing_city');

    locationSelectElement = document.getElementById('outing_location');

    locationFields = {
        street: document.getElementById('street'),
        latitude: document.getElementById('latitude'),
        longitude: document.getElementById('longitude')
    }
    postalCodeField = document.getElementById('postalCode');

    /**
     * We initializeFields by running update function once the page is loaded.
     */
    updateLocationsOptions();
    updateLocationFields();

    /**
     * We run the update functions every time the selectors values change
     * @type {updateLocationsOptions}
     */
    citySelectElement.onchange = updateLocationsOptions;
    locationSelectElement.onchange = updateLocationFields;
}


function updateLocationsOptions() {
    const cityData = {"cityName": citySelectElement.value};

    fetch('ajax-cityData', {method: 'POST', body: JSON.stringify(cityData)})
        .then(response => response.json())
        .then(data => {

            currentAjaxData = data;

            clearOptions(locationSelectElement);

            Object
                .keys(data['locations'])
                .map(loc => {
                    const optionElement = document.createElement('option');
                    optionElement.text = data['locations'][loc]['name'];
                    return optionElement;
                })
                .forEach(option => {
                    locationSelectElement.add(option);
                })

            postalCodeField.innerText = data['postalCode'];

            let locationsArray = Object.values(data['locations']);

            /**
             * Check if city has location : if yes update fields, if no clean fields
             */
            (locationsArray.length)?
                updateLocationFields()
                :
                cleanLocationFields();

        });

}

function clearOptions(selectElement) {
    let i, length = selectElement.options.length - 1;
    for (i = length; i >= 0; i--) {
        selectElement.remove(i);
    }
}

function updateLocationFields() {
    /**
     * Delay the update of location fields in order to wait for the ajax data to be fetched
     * @returns {number}
     */
    let delayedUpdate = () => setTimeout(() => {
        let locationName = locationSelectElement.value;
        locationFields.street.innerText = currentAjaxData['locations'][locationName]['street'];
        locationFields.latitude.innerText = currentAjaxData['locations'][locationName]['latitude'];
        locationFields.longitude.innerText = currentAjaxData['locations'][locationName]['longitude'];
    }, 700);
    delayedUpdate();
}

function cleanLocationFields() {
    locationFields.street.innerText = "";
    locationFields.latitude.innerText = "";
    locationFields.longitude.innerText = "";
}
