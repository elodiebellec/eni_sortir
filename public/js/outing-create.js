let citySelectElement;
let postalCodeField;

let locationSelectElement;
let locationFields;

let currentAjaxData;

window.onload = init;

function init() {
    /**
     * We reference all the HTML fields we'll need by affecting the global variables
     * declared at the beginning of the file.
     */
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
    captureRequiredDomElements();

    /**
     * Initialize the data when the page is loaded :
     * We fetch locations options relative to the selected city,
     * then, we wait for the ajax data arrival to update the
     * location fields (street, coordinates etc...)
     */
    async function fetchLocationsOptions() {

        const cityData = {"cityName": citySelectElement.value};

        function generateOptionElement(location) {
            const optionElement = document.createElement('option');

            optionElement.text = location['name'];
            optionElement.innerText = location['name'];

            optionElement.id = location['id'];
            optionElement.value = location['id'];

            return optionElement;
        }

        return fetch('ajax-cityData', {
            method: 'POST',
            body: JSON.stringify(cityData)
        })
            .then(response => response.json())
            .then(data => {

                currentAjaxData = data;

                /**
                 * Erase previous location options
                 * @param selectElement
                 */
                function clearOptionsElements(selectElement) {
                    let i, length = selectElement.options.length - 1;
                    for (i = length; i >= 0; i--) {
                        selectElement.remove(i);
                    }
                }
                clearOptionsElements(locationSelectElement);

                /**
                 * Create array of <option> "location name" </option> from JSON Data.
                 */
                Object
                    .keys(data['locations'])
                    .map(key => generateOptionElement(data['locations'][key]))
                    .forEach(optionElement => locationSelectElement.add(optionElement))

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
        (locationsArray.length) ?
            setLocationInfos()
            :
            cleanLocationInfos();
    }
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





