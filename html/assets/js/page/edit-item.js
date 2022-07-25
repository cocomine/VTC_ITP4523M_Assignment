console.log("script loading...");
const table = $("#dataTable").DataTable();
let itemList = {};

//get item list
$.ajax({
    url: "?get-item=1",
    method: 'POST',
    dataType: "json",
    success: function(data){
        let output = [];
        data.forEach(function(data){
            //console.log(data);

            //generate row
            output.push([
                data.ItemID,
                data.Name,
                data.Stock,
                "$ "+data.Price,
                data.Description,
                `<i class="fa fa-edit" data-ss-itemid="${data.ItemID}" data-ss-action="edit" title="Edit Item info">`
            ]);

            //add in item list
            itemList[data.ItemID] = {
                "Name" : data.Name,
                "Stock": data.Stock,
                "Price": data.Price,
                "Description": data.Description
            };
        });

        //draw table
        table.rows.add(output).draw();
    },
    error: ajax_Error
})

//edit item
$("#dataTable > tbody").on("click", "[data-ss-action=\"edit\"]", function(){
    let itemID = $(this).data("ss-itemid");

    //setup value
    $("#itemName").val(itemList[itemID]['Name']);
    $("#iDescription").val(itemList[itemID]['Description']);
    $("#stockQty").val(itemList[itemID]['Stock']);
    $("#itemPrice").val(itemList[itemID]['Price']);
    $("#editItem").attr("data-ss-itemid", itemID);

    //show
    let modal = bootstrap.Modal.getOrCreateInstance($("#edit-Item"));
    modal.show();
})

//update item
$("#editItem").submit(function(e){
    if(!e.isDefaultPrevented()){
        e.preventDefault() //stop submit

        toastr.info("Please wait for a moment...");
        let data = objectifyForm($(this).serializeArray());
        let itemID = $(this).data("ss-itemid");

        //send
        $.ajax({
            url: "?edit-item=" + itemID,
            method: 'POST',
            dataType: "json",
            data: JSON.stringify(data),
            contentType: "text/json",
            success: function(result){
                toastr.clear();
                if(result.result === "Success"){
                    let row = table.row(function(idx, data, node){
                        return data[0] === itemID;
                    });

                    //update display
                    let rowData = row.data();
                    console.log(rowData)
                    rowData[1] = data.itemName;
                    rowData[2] = data.stockQty;
                    rowData[3] = "$ "+data.itemPrice;
                    rowData[4] = data.iDescription;
                    row.data(rowData).draw();

                    let modal = bootstrap.Modal.getOrCreateInstance($("#edit-Item"));
                    modal.hide();

                    toastr.success("Item information update success!", "Update success");
                }else{
                    toastr.error("Item information update fail!", "Update fail");
                }
            },
            error: ajax_Error
        });
    }
})

//add item
$("#addItem").submit(function(e){
    if(!e.isDefaultPrevented()){
        e.preventDefault() //stop submit

        toastr.info("Please wait for a moment...");
        let data = objectifyForm($(this).serializeArray());

        //send
        $.ajax({
            url: "?add-item=1",
            method: 'POST',
            dataType: "json",
            data: JSON.stringify(data),
            contentType: "text/json",
            success: function(result){
                toastr.clear();
                if(result.result === "Success"){
                    //update display
                    table.row.add([
                        result.data.ItemID,
                        data.itemName,
                        data.stockQty,
                        "$ "+data.itemPrice,
                        data.iDescription,
                        `<i class="fa fa-edit" data-ss-itemid="${result.data.ItemID}" data-ss-action="edit">`
                    ]).draw();

                    toastr.success("Item add success! <br> ItemID:"+result.data.ItemID, "Update success");
                }else{
                    toastr.error("Item add fail!", "Update fail");
                }
            },
            error: ajax_Error
        });
    }
})