module.exports = {

    getValueFromDataFile: function (inputValue) {
        let value = "";
        try {
            if (inputValue != null) {
                if (inputValue.includes('.')) {
                    let tempVal = inputValue.split(".");
                    let dataFile = tempVal[0];
                    let val = tempVal[1];
                    const fileName = require('../Data/components.json');
                    value = fileName[val];
                } else if (inputValue == "ENV") {
                    value = inputValue;
                }
            }
            return value;

        } catch (err) {
            console.log('Error is : >>>>>>>>>   ', err)
        }
    }
}