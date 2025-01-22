document.addEventListener('DOMContentLoaded', function () {
	const checkAll = document.getElementById('checkAll')
	const checkboxes = document.querySelectorAll('.download-checkbox')
	const fab = document.getElementById('fab')
	const form = document.getElementById('download')
	const selectedSize = document.getElementById('selectedSize')
	const jsonInput = document.getElementById('downloadCheckboxJson')
	const files = JSON.parse(document.getElementById('data-files').dataset.files)

	//Checkbox handler
	const handleCheckAllChange = () => {
		checkboxes.forEach(checkbox => {
			checkbox.checked = checkAll.checked
		})
		updateFabClass()
		updateSelectedCount()
		updateSelectedSize()
	}

	const updateFabClass = () => {
		const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked)
		const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked)

		if (anyChecked) {
			fab.className = 'position-fixed btn btn-primary bottom-0 end-0 m-5'
			selectedSize.className = 'btm-default form-label p-2'
		} else {
			fab.className = 'position-fixed btn btn-default disabled bottom-0 end-0 m-5'
			selectedSize.className = 'btm-default form-label p-2 d-none'
		}

		// Update "Check All" state
		if (allChecked) {
			checkAll.checked = true
		} else if (!anyChecked) {
			checkAll.checked = false
		}
	}

	checkAll.addEventListener('change', handleCheckAllChange)
	checkboxes.forEach(checkbox => {
		checkbox.addEventListener('change', () => {
			updateFabClass()
			updateSelectedCount()
			updateSelectedSize()
		})
	})

	// Form submission manipulator
	form.addEventListener('submit', function (event) {
		event.preventDefault()

		const selectedValues = Array.from(checkboxes)
			.filter(checkbox => checkbox.checked)
			.reduce((acc, checkbox) => {
				acc[checkbox.id] = checkbox.value
				return acc
			}, {})

		jsonInput.value = JSON.stringify(selectedValues)

		form.submit()
	})

	const updateSelectedCount = () => {
		const count = Array.from(checkboxes).filter(checkbox => checkbox.checked).length
		selectedCount.textContent = `${count} selected`
	}

	const updateSelectedSize = () => {
		let total = 0;
	
		const checkedCheckboxes = Array.from(checkboxes).filter(checkbox => checkbox.checked);
	
		checkedCheckboxes.forEach(checkbox => {
			files.every(file => {
				if (file.identifier == checkbox.value) {
					total += file.size
					return false
				}
				return true
			})
		})

		const selectedSizeValue = document.getElementById('selectedSizeValue')
		if (selectedSizeValue) {
			selectedSizeValue.innerHTML = formatBytes(total)
		}
	};

	function formatBytes(bytes, decimals = 2) {
		if (!+bytes) return '0 Bytes'
	
		const k = 1024
		const dm = decimals < 0 ? 0 : decimals
		const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
	
		const i = Math.floor(Math.log(bytes) / Math.log(k))
	
		return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
	}

	// ------------------------------------- Search suggestions -----------------------------

	var fileNames = []
	var pathNames = []

	files.forEach(file => {
		fileNames.push(file.name)
		pathNames.push(file.identifier)
	})

	function autocomplete(inp, arr) {
		var currentFocus
		inp.addEventListener('input', function (e) {
			var a,
				b,
				i,
				val = this.value
			closeAllLists()
			if (!val) {
				return false
			}
			currentFocus = -1
			a = document.createElement('DIV')
			a.setAttribute('id', this.id + 'autocomplete-list')
			a.setAttribute('class', 'autocomplete-items')
			this.parentNode.appendChild(a)

			let matchFound = false

			for (i = 0; i < arr.length; i++) {
				if (arr[i].toUpperCase().includes(val.toUpperCase()) && val.length >= 2) {
					matchFound = true // A match is found
					b = document.createElement('DIV')
					var startIndex = arr[i].toUpperCase().indexOf(val.toUpperCase())
					b.innerHTML =
						arr[i].substring(0, startIndex) +
						'<strong>' +
						arr[i].substr(startIndex, val.length) +
						'</strong>' +
						arr[i].substring(startIndex + val.length)

					b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>"
					b.addEventListener('click', function (e) {
						inp.value = this.getElementsByTagName('input')[0].value
						closeAllLists()
					})
					a.appendChild(b)
				}
			}
			if (!matchFound && val.length >= 2) {
				b = document.createElement('DIV')
				b.innerHTML = document.getElementById('nothingFound').innerHTML
				b.classList.add('no-results')
				a.appendChild(b)
			}
		})

		inp.addEventListener('keydown', function (e) {
			var x = document.getElementById(this.id + 'autocomplete-list')
			if (x) x = x.getElementsByTagName('div')
			if (e.keyCode == 40) {
				currentFocus++
				addActive(x)
			} else if (e.keyCode == 38) {
				//up
				currentFocus--
				addActive(x)
			} else if (e.keyCode == 13) {
				e.preventDefault()
				if (currentFocus > -1) {
					if (x) x[currentFocus].click()
				}
			}
		})

		function addActive(x) {
			if (!x) return false
			removeActive(x)
			if (currentFocus >= x.length) currentFocus = 0
			if (currentFocus < 0) currentFocus = x.length - 1
			x[currentFocus].classList.add('autocomplete-active')
		}

		function removeActive(x) {
			for (var i = 0; i < x.length; i++) {
				x[i].classList.remove('autocomplete-active')
			}
		}

		function closeAllLists(elmnt) {
			var x = document.getElementsByClassName('autocomplete-items')
			for (var i = 0; i < x.length; i++) {
				if (elmnt != x[i] && elmnt != inp) {
					x[i].parentNode.removeChild(x[i])
				}
			}
		}

		document.addEventListener('click', function (e) {
			closeAllLists(e.target)
		})
	}

	autocomplete(document.getElementById('nameInput'), fileNames)
	autocomplete(document.getElementById('pathInput'), pathNames)
})
