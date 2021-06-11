let citySelectElement;
let postalCodeField;

let locationSelectElement;
let locationFields;

let currentAjaxData;

window.onload = init;

function init() {
    /**
     * We reference all the HTML fields we will need by affecting the global variables declared
     * at the beginning of the file.
     */
    captureRequiredDomElements();

    /**
     * We fetch locations options relative to the selected city,
     * then, we wait for the ajax data arrival to update the
     * location fields (street, coordinates etc...)
     */
    fetchLocationsOptions().then(_ => updateLocationsFields(currentAjaxData));

    /**
     * We run the update functions every time the city value change
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
             * Create array of <option> "location name" </option> from JSON Data.
             */
            Object
                .keys(data['locations'])
                .map(index => {
                    const optionElement = document.createElement('option');

                    optionElement.text = data['locations'][index]['name'];
                    optionElement.innerText = data['locations'][index]['name'];

                    optionElement.id = data['locations'][index]['id'];
                    optionElement.value = data['locations'][index]['id'];

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
    let locationName = locationSelectElement.options[locationSelectElement.selectedIndex].innerHTML;
    locationFields.street.innerText = currentAjaxData['locations'][locationName]['street'];
    locationFields.latitude.innerText = currentAjaxData['locations'][locationName]['latitude'];
    locationFields.longitude.innerText = currentAjaxData['locations'][locationName]['longitude'];

}
function cleanLocationInfos() {
    locationFields.street.innerText = "";
    locationFields.latitude.innerText = "";
    locationFields.longitude.innerText = "";
}
