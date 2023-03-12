(function() {

   // 33 can't be cause it's the initial code =)
   const randomInt = (min = 0, max = 10000) => { 
      let n = Math.floor(Math.random() * (max - min + 1) + min)
      return n === 33 ? randomInt() : n
   }
   
   const form = document.querySelector('form')
   form.onsubmit = e => {
      let allInputsHaveValue = true
      const inputs = form.querySelectorAll('input:not([type="datetime-local"])')
      inputs.forEach(input => {
         const isEmpty = input.value === ''
         if(isEmpty) {
            input.classList.add('is-invalid')
            allInputsHaveValue = false
         } else {
            input.classList.remove('is-invalid')
         }
      })

      if(!allInputsHaveValue) {
         e.preventDefault()
         return
      }

      const priceInputs = form.querySelectorAll('[data-money]')
      priceInputs.forEach(input => {
         input.value = removeMoneyMask(input.value)
      })
   }

   const btnAddRow = document.querySelector('#btn-add-row')
   btnAddRow.onclick = addRow

   const btnDownload = document.querySelector('#btn-submit-download') 
   const btnSubmit   = document.querySelector('#btn-submit') 

   btnDownload.onclick = e => {
      form.setAttribute('action', '/?download=true')
   }

   btnSubmit.onclick = e => {
      form.setAttribute('action', '/')
   }

   const inputsContainer = document.querySelector('.inputs-wrapper')
   function addRow() {

      const code = randomInt()

      inputsContainer.insertAdjacentHTML('beforeend', `
         <div class="form-row mt-2" data-row="${code}">
            <div class="col-md-2">
               <label for="code-${code}">Código</label>
               <input type="number" class="form-control" id="code-${code}" name="code[]" value="${code}">
            </div>
            <div class="col-md-4">
               <label for="description-${code}">Nome</label>
               <input type="text" class="form-control" id="description-${code}" name="description[]">
            </div>
            <div class="col-md-4">
               <label for="price-${code}">Preço</label>
               <input type="text" class="form-control" data-price id="price-${code}" name="price[]" onkeyup="this.value = moneyMask(this.value)">
            </div>
            <div class="col-md-2">
               <label>&nbsp;&nbsp;&nbsp;</label> <br>
               <button type="button" class="btn btn-danger" data-btn-delete-row="${code}">Apagar Produto <i class="fas fa-trash"></i></button>
            </div>
         </div>
      `)

      const currentBtn = document.querySelector(`[data-btn-delete-row="${code}"]`)
      currentBtn.onclick = () => deleteRow(code)
      
      setProductsCount()
   }

   function deleteRow(rowNumber) {
      const row = document.querySelector(`.form-row[data-row="${parseInt(rowNumber)}"]`)
      console.log(rowNumber);
      if(!row)
         return
      
      row.remove()
      setProductsCount()
   }

   const productsCountElement = document.querySelector('#products-count')
   function setProductsCount () {
      const rowCount = document.querySelectorAll('.form-row[data-row]').length
      productsCountElement.textContent = rowCount
   }
   
})()

// Funções helpers no escopo global

function moneyMask(value) {
   value = value + '';
	value = parseInt(value.replace(/[\D]+/g, ''));
	value = value + '';
	value = value.replace(/([0-9]{2})$/g, ",$1");

	if (value.length > 6) {
		value = value.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
	} 

	if (value == 'NaN') {
		value = '';
	}
	return value;
}

function removeMoneyMask(value) {
   value = String(value)
	if (value === "") {
		value = 0;
	} else {
		value = value.replace(".", "");
		value = value.replace(",", ".");
		value = parseFloat(value);
	}
	return value;
}