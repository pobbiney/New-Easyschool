<script>
    (function () {
        const rowsContainer = document.getElementById('qualificationRows');
        const addButton = document.getElementById('addQualificationRow');
        const template = document.getElementById('qualificationRowTemplate');
        const removedContainer = document.getElementById('removedQualificationIds');

        if (!rowsContainer || !addButton || !template) {
            return;
        }

        function nextIndex() {
            return rowsContainer.querySelectorAll('[data-qualification-row]').length;
        }

        function refreshRowNumbers() {
            rowsContainer.querySelectorAll('[data-qualification-row]').forEach(function (row, index) {
                const numberEl = row.querySelector('[data-qualification-number]');
                if (numberEl) {
                    numberEl.textContent = index + 1;
                }
            });
        }

        function reindexRow(row, index) {
            row.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/qualifications\[[^\]]+]/, 'qualifications[' + index + ']');
            });
        }

        function reindexAllRows() {
            rowsContainer.querySelectorAll('[data-qualification-row]').forEach(function (row, index) {
                reindexRow(row, index);
            });
            refreshRowNumbers();
        }

        function ensureAtLeastOneRow() {
            if (rowsContainer.querySelectorAll('[data-qualification-row]').length === 0) {
                addRow();
            }
        }

        function addRow() {
            const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;
            if (row) {
                rowsContainer.appendChild(row);
                refreshRowNumbers();
            }
        }

        addButton.addEventListener('click', function () {
            addRow();
        });

        rowsContainer.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-qualification-row');
            if (!removeButton) {
                return;
            }

            const row = removeButton.closest('[data-qualification-row]');
            if (!row) {
                return;
            }

            const existingId = removeButton.getAttribute('data-existing-id');
            if (existingId && removedContainer) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'removed_qualification_ids[]';
                hidden.value = existingId;
                removedContainer.appendChild(hidden);
            }

            row.remove();
            reindexAllRows();
            ensureAtLeastOneRow();
        });

        refreshRowNumbers();
    })();
</script>
