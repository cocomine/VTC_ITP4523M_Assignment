console.log("script loading...");
const table = $("#dataTable").DataTable();
let itemList = {};
let shoppingCart = {};

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
                `<i class="fa fa-cart-plus" data-ss-action="addCart" data-ss-itemid="${data.ItemID}" title="Add into shopping cart"></i>`
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

//add item to cart
$("#dataTable > tbody").on("click", "[data-ss-action=\"addCart\"]", function(){
    let itemID = $(this).data("ss-itemid");

    //add to cart
    shoppingCart[itemID] = 1;
    $("#shoppingCart > tbody").append(`
        <tr>
            <td>${itemID}</td>
            <td>${itemList[itemID]['Name']}</td>
            <td>
                <input type="number" min="1" max="100" value="1" data-ss-itemid="${itemID}" data-ss-action="item-qty" aria-label="Quantity">
            </td>
            <td>
                <i class="fa fa-times" data-ss-itemid="${itemID}" data-ss-action="delete" title="Remove from shopping cart"></i>
            </td>
        </tr>`);

    //remove from item list
    table.row($(this).parents("tr")).remove().draw();

    //console.log(shoppingCart)
})

//remove item from cart
$("#shoppingCart > tbody").on("click", "[data-ss-action=\"delete\"]", function(){
    let itemID = $(this).data("ss-itemid");

    //remove from cart
    delete shoppingCart[itemID];
    $(this).parents("tr").remove();

    //add to item list
    table.row.add([
        itemID,
        itemList[itemID]['Name'],
        itemList[itemID]['Stock'],
        "$ "+itemList[itemID]['Price'],
        itemList[itemID]['Description'],
        `<i class="fa fa-cart-plus" data-ss-action="addCart" data-ss-itemid="${itemID}" title="Add into shopping cart"></i>`
    ]).draw();

    //console.log(shoppingCart)
})

//add item qty
.on("change", "[data-ss-action=\"item-qty\"]", function(){
    let itemid = $(this).data("ss-itemid");
    shoppingCart[itemid] = parseInt($(this).val());

    //console.log(shoppingCart)
})

//create order
$("#create-order").submit(function(e){
    if(!e.isDefaultPrevented()){
        e.preventDefault() //stop submit

        toastr.info("Please wait for a moment...");
        let data = objectifyForm($(this).serializeArray());
        data["items"] = shoppingCart; //put item list
        //console.log(data);

        //send request
        $.ajax({
            url: "?create-order=1",
            method: 'POST',
            dataType: "json",
            data: JSON.stringify(data),
            contentType: "text/json",
            success: function(result){
                toastr.clear();
                if(result.result === "Success"){
                    toastr.success("Order create success! <br>Order ID:"+result.data.orderID+" Total:$"+result.data.Total, "Create success");
                }else{
                    toastr.error("Order create fail!", "Create fail");
                }
            },
            error: ajax_Error
        });
    }
})

//need delivery
$("#needDelivery").change(function(){
    if($(this).prop("checked")){
        $("#dDate, #dAddress").prop("disabled", false);
    }else{
        $("#dDate, #dAddress").prop("disabled", true);
    }
})