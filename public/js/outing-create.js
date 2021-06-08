let citySelectElement;
let locationSelectElement;
let currentAjaxData;
let locationFields;
let postalCodeField;
window.onload = init;

function init() {
    /**
     * We reference all the HTML fields we will need by affecting the global variables declared
     * at the beginning of the file.
     */
    captureRequiredDomElements();

    /**
     * We fetch locations options relative to the selected city,
     * then we wait the ajax data arrival to update the
     * fields related to the current selected location
     */
    fetchLocationsOptions().then(_ => updateLocationsFields(currentAjaxData));

    /**
     * We run the update functions every time the selectors values change
     */
    citySelectElement.onchange = () => {
        fetchLocationsOptions().then(_ => updateLocationsFields(currentAjaxData));
    }

    locationSelectElement.onchange = () => {
        updateLocationsFields(currentAjaxData);
    }
}
function captureRequiredDomElements() {
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
}

async function fetchLocationsOptions() {
    const cityData = {"cityName": citySelectElement.value};

    return fetch('ajax-cityData', {method: 'POST', body: JSON.stringify(cityData)})
        .then(response => response.json())
        .then(data => {

            currentAjaxData = data;

            clearOptionsElements(locationSelectElement);

            /**
             * Create array of <option> "location name" </option> from JSON File.
             */
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
            updateLocationsFields(data);

        });

}

function updateLocationsFields(data) {
    /**
     * Convert JSON to js Array.
     */
    let locationsArray = Object.values(data['locations']);

    /**
     * Check if city has location : if yes update fields, if no clean fields
     */
    (locationsArray.length) ?
        setLocationInfos()
        :
        cleanLocationInfos();
}

function clearOptionsElements(selectElement) {
    let i, length = selectElement.options.length - 1;
    for (i = length; i >= 0; i--) {
        selectElement.remove(i);
    }
}

function setLocationInfos() {
    let locationName = locationSelectElement.value;
    locationFields.street.innerText = currentAjaxData['locations'][locationName]['street'];
    locationFields.latitude.innerText = currentAjaxData['locations'][locationName]['latitude'];
    locationFields.longitude.innerText = currentAjaxData['locations'][locationName]['longitude'];

}
function cleanLocationInfos() {
    locationFields.street.innerText = "";
    locationFields.latitude.innerText = "";
    locationFields.longitude.innerText = "";
}
