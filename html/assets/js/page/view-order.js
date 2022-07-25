console.log("script loading...");
const table = $("#dataTable").DataTable();

//get order list
$.ajax({
    url: "?get-order=1",
    method: 'POST',
    dataType: "json",
    success: function(data){
        let output = [];
        data.forEach(function(data){
            //generate row
            output.push([
                data.OrderID,
                data.Name,
                data.Email,
                data.Phone,
                data.StaffID,
                data.StaffName,
                data.DateTime,
                data.Address,
                data.DeliveryDate,
                '$ '+data.Total,
                `<i class="fa fa-edit" data-ss-orderid="${data.OrderID}" data-ss-action="edit" title="Edit delivery info"></i>&nbsp;
                 <i class="fa fa-cubes" data-ss-orderid="${data.OrderID}" data-ss-action="show-items" title="Show order item list"></i>&nbsp;
                 <i class="fa fa-trash-o" data-ss-orderid="${data.OrderID}" data-ss-action="delete" title="Delete order"></i>`
            ]);
        });

        //draw table
        table.rows.add(output).draw();
    },
    error: ajax_Error
})

//remove order
$("#dataTable tbody").on("click", "[data-ss-action=\"delete\"]", function(e){
    toastr.info("Please wait for a moment...");
    $.ajax({
        url: "?delete-order="+$(this).data("ss-orderid"),
        method: 'POST',
        dataType: "json",
        success: function(data){
            //console.log(data);
            toastr.clear();
            if(data.result === "Success"){
                table.row($(e.target).parents('tr')).remove().draw();
                toastr.success("Order delete success!", "Delete success");
            }else{
                toastr.error("Order delete fail!", "Delete fail");
            }
        },
        error: ajax_Error
    })
})

//show order item
.on("click", "[data-ss-action=\"show-items\"]", function(){
    toastr.info("Please wait for a moment...");
    $.ajax({
        url: "?order-items="+$(this).data("ss-orderid"),
        method: 'POST',
        dataType: "json",
        success: function(data){
            //console.log(data);
            let output = "";
            data.forEach(function(data){
                output += `
                 <tr>
                     <td>${data.ItemID}</td>
                     <td>${data.Name}</td>
                     <td>${data.Qty}</td>
                     <td>$ ${data.TotalPrice}</td>
                 </tr>`;
            });

            //show output
            $("#orderItems > tbody").html(output)
            let modal = bootstrap.Modal.getOrCreateInstance($("#order-item")[0]);
            modal.show();

            toastr.clear();
        },
        error: ajax_Error
    })
})

//show delivery edit form
.on("click", "[data-ss-action=\"edit\"]", function(){
    toastr.info("Please wait for a moment...");
    $.ajax({
        url: "?order-info="+$(this).data("ss-orderid"),
        method: 'POST',
        dataType: "json",
        success: function(data){
            //console.log(data);
            $("#dAddress").val(data.Address);
            $("#dDate").val(data.Date);
            $("#editOrder").attr("data-ss-orderid", data.OrderID)

            let modal = bootstrap.Modal.getOrCreateInstance($("#edit-order")[0]);
            modal.show();
            toastr.clear();
            },
        error: ajax_Error
    });
})

//update delivery info
$("#editOrder").submit(function(e){
    if(!e.isDefaultPrevented()){
        e.preventDefault() //stop submit

        toastr.info("Please wait for a moment...");
        let data = objectifyForm($(this).serializeArray());
        let orderID = $(this).data("ss-orderid");

        $.ajax({
            url: "?update-order=" + orderID,
            method: 'POST',
            dataType: "json",
            data: JSON.stringify(data),
            contentType: "text/json",
            success: function(result){
                toastr.clear();
                if(result.result === "Success"){
                    let row = table.row(function(idx, data, node){
                        return data[0] === orderID.toString();
                    });

                    //update display
                    let rowData = row.data();
                    rowData[7] = data.dAddress;
                    rowData[8] = data.dDate;
                    row.data(rowData).draw();

                    //hide modal
                    let modal = bootstrap.Modal.getOrCreateInstance($("#edit-order")[0]);
                    modal.hide();
                    toastr.success("Order delivery information update success!", "Update success");
                }else{
                    toastr.error("Order delivery information update fail!", "Update fail");
                }
            },
            error: ajax_Error
        });
    }
})