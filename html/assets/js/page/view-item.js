console.log("script loading...");
const table = $("#dataTable").DataTable();

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
                data.Description
            ]);
        });

        //draw table
        table.rows.add(output).draw();
    },
    error: ajax_Error
})