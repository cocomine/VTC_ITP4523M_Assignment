//handle ajax error
function ajax_Error(xhr, textStatus){
    if(xhr.status === 400){
        toastr.error("Invalid request!", "400")
        return;
    }
    if(xhr.status === 403){
        toastr.error("Access Denied!", "403")
        return;
    }
    if(textStatus === "timeout"){
        toastr.error("Access Timeout!", "Timeout")
        return;
    }
    toastr.error("Unknown Error!", "Error")
}

//Dynamically load scripts
$(document).ready(function(){
    load_script.forEach(function(data){
        $.getScript(data).fail(function(xhr, settings){
            toastr.error("Can't load "+data, "Unable to load script");
        });
    })
})

//turn to json
function objectifyForm(formArray) {
    let returnArray = {};
    for (let i = 0; i < formArray.length; i++){
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}