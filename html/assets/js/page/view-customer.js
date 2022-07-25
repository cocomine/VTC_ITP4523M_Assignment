console.log("script loading...");
const table = $("#dataTable").DataTable();

$.ajax({
    url: "?get-customer=1",
    method: 'POST',
    dataType: "json",
    success: function(data){
        let output = [];
        data.forEach(function(data){
            //generate row
            output.push([
                data.Name,
                data.Email,
                data.Phone,
                `<i class="fa fa-trash-o" data-ss-email="${data.Email}" data-ss-action="delete" title="Delete customer"></i>`
            ]);
        });

        //draw table
        table.rows.add(output).draw();
    },
    error: ajax_Error
})

//delete customer
$("#dataTable > tbody").on("click", "[data-ss-action=\"delete\"]", function(e){
    let email = $(this).data("ss-email");

    toastr.info("Please wait for a moment...");
    $.ajax({
        url: "?delete-customer="+email,
        method: 'POST',
        dataType: "json",
        success: function(data){
            //console.log(data);
            toastr.clear();
            if(data.result === "Success"){
                table.row($(e.target).parents('tr')).remove().draw();
                toastr.success("Customer delete success!", "Delete success");
            }else{
                toastr.error("Customer delete fail!", "Delete fail");
            }
        },
        error: ajax_Error
    })
})