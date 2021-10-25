require([
    "jquery",
    "mage/validation"
],function($) {
    $(document).ready(function() {
        let searchForm = $("#productSearch");
        let tableContainer = $("#productTable");
        let tableBody = document.querySelector("#productTableBody");
        let searchFormErrorContainer = document.querySelector("#formError");

        //set validation
        searchForm.mage('validation', {});

        /**
         * handle errors
         * @param renderData
         */
        let render = (renderData = null) => {
            if (renderData.errors.length > 0) {
                searchFormErrorContainer.innerHTML = renderData.errors;
            } else if (renderData.products){
                renderProducts(renderData.products);
            } else {
                resetRender();
            }
        }

        /**
         * reset render
         */
        let resetRender = () => {
            //errors
            searchFormErrorContainer.innerHTML = "";

            //table
            tableBody.innerHTML = "";
            tableContainer.hide();

        }

        /**
         * render products in table
         * @param products
         */
        let renderProducts = (products) => {

            for (let product of products) {
                let tableRow = document.createElement('tr');

                //image
                let imageData = document.createElement('td')
                let image = document.createElement("img");
                image.src = product.thumbnail;
                imageData.appendChild(image);
                tableRow.appendChild(imageData);
                //sku
                let skuData = document.createElement('td');
                skuData.textContent = product.sku;
                tableRow.appendChild(skuData);
                //name
                let nameData = document.createElement('td');
                nameData.textContent = product.name;
                tableRow.appendChild(nameData);
                //qty
                let qtyData = document.createElement('td');
                qtyData.textContent = product.qty;
                tableRow.appendChild(qtyData);
                //price
                let priceData = document.createElement('td');
                priceData.textContent = '$ ' + product.price;
                tableRow.appendChild(priceData);
                //link
                let linkData = document.createElement('td');
                let link = document.createElement('a');
                link.href = product.linkToProduct;
                link.textContent = "view product"
                link.target = '_blank';
                linkData.appendChild(link)
                tableRow.appendChild(linkData);

                tableBody.appendChild(tableRow);
            }

            tableContainer.show();
        }

        //handle submit
        searchForm.submit(function( event ) {
            event.preventDefault();

            resetRender();

            //if validated send ajax
            if (searchForm.validation("isValid")) {
                $.ajax({
                    url: "/Customer/Product/ProductsByPrice?" + searchForm.serialize(),
                    type: 'GET',
                    showLoader: true,
                    dataType: "json",
                    success: (data) => { render(data) },
                    error: (xhr, status, errorThrown) => {
                        render({error: 'Error searching for products, try again.'})
                        console.log(errorThrown);
                    }
                });
            }
        });
    });
});
