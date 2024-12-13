document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll')
    const checkboxes = document.querySelectorAll('.download-checkbox')
    const fab = document.getElementById('fab')
    const form = document.getElementById('download')
    const jsonInput = document.getElementById('downloadCheckboxJson')

    //Checkbox handler
    const handleCheckAllChange = () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = checkAll.checked
        })
        updateFabClass()
        updateSelectedCount()
    }

    const updateFabClass = () => {
        const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked)
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked)

        if (anyChecked) {
            fab.className = 'position-fixed btn btn-primary bottom-0 end-0 m-5'
        } else {
            fab.className = 'position-fixed btn btn-default disabled bottom-0 end-0 m-5'
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
})