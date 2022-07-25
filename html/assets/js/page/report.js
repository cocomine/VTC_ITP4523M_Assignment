console.log("script loading...");
let showDate = new Date();
get_report();

function get_report(){
    $("#showMonth").text((showDate.getMonth()+1)+" / "+showDate.getFullYear())

    $.ajax({
        url: "?report-date="+(showDate.getMonth()+1)+"-"+showDate.getFullYear(),
        method: 'POST',
        dataType: "json",
        success: function(data){
            let output = "";
            data.forEach(function(data){
                //generate row
                output += `
            <tr>
                <td>${data.StaffID}</td>
                <td>${data.Name}</td>
                <td>${data.Orders || 0}</td>
                <td>$ ${data.Total || 0}</td>
            </tr>`;
            });

            $("#monReport > tbody").html(output);
        },
        error: ajax_Error
    })
}

//last month
$("[data-ss-action=\"last\"]").click(function(){
    showDate.setMonth(showDate.getMonth()-1);
    get_report()
});
//next month
$("[data-ss-action=\"next\"]").click(function(){
    showDate.setMonth(showDate.getMonth()+1);
    get_report()
});