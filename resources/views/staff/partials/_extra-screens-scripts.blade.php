<script>
    (function () {
        window.initStaffExtraScreens = function (options) {
            const accessToggle = options.accessToggle;
            const accessFields = options.accessFields;
            const categorySelect = options.categorySelect;
            const previewBox = options.previewBox;
            const extraCheckboxes = document.querySelectorAll('.extra-link-checkbox');
            const categoryLinksUrl = options.categoryLinksUrl;
            const accessSection = document.getElementById('system-access-section');
            let savedAccessLinkIds = options.savedAccessLinkIds || [];
            let savedAccessLinks = options.savedAccessLinks || [];

            if (accessSection) {
                if (!savedAccessLinkIds.length && accessSection.dataset.savedAccessLinkIds) {
                    try {
                        savedAccessLinkIds = JSON.parse(accessSection.dataset.savedAccessLinkIds).map(function (id) {
                            return parseInt(id, 10);
                        });
                    } catch (error) {
                        savedAccessLinkIds = [];
                    }
                }

                if (!savedAccessLinks.length && accessSection.dataset.savedAccessLinks) {
                    try {
                        savedAccessLinks = JSON.parse(accessSection.dataset.savedAccessLinks);
                    } catch (error) {
                        savedAccessLinks = [];
                    }
                }
            }

            const hasSavedAccessLinks = savedAccessLinkIds.length > 0;

            function updateExtraScreenTile(checkbox) {
                const tile = checkbox.closest('.extra-screen-tile');
                if (!tile) return;
                tile.classList.toggle('is-selected', checkbox.checked);
            }

            function updateExtraScreensCount() {
                const countEl = document.getElementById('extra-screens-count');
                if (!countEl) return;

                const count = Array.from(extraCheckboxes).filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                countEl.textContent = count + ' selected';
            }

            function applySavedAccessLinks() {
                extraCheckboxes.forEach(function (checkbox) {
                    const linkId = parseInt(checkbox.value, 10);
                    checkbox.checked = savedAccessLinkIds.includes(linkId);
                    delete checkbox.dataset.categorySelected;
                    updateExtraScreenTile(checkbox);
                });

                updateExtraScreensCount();
            }

            function syncExtraCheckboxes(inheritedLinkIds, updateChecks) {
                const shouldUpdateChecks = updateChecks !== false && !hasSavedAccessLinks;

                extraCheckboxes.forEach(function (checkbox) {
                    const tile = checkbox.closest('.extra-screen-tile');
                    const linkId = parseInt(checkbox.value, 10);
                    const isInherited = inheritedLinkIds.includes(linkId);
                    const wasCategorySelected = checkbox.dataset.categorySelected === 'true';

                    checkbox.disabled = false;

                    if (tile) {
                        tile.classList.toggle('is-inherited', isInherited);
                    }

                    if (shouldUpdateChecks) {
                        if (isInherited) {
                            checkbox.checked = true;
                            checkbox.dataset.categorySelected = 'true';
                        } else if (wasCategorySelected) {
                            checkbox.checked = false;
                            delete checkbox.dataset.categorySelected;
                        }
                    }

                    updateExtraScreenTile(checkbox);
                });

                updateExtraScreensCount();
            }

            extraCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (!this.checked) {
                        delete this.dataset.categorySelected;
                    }
                    updateExtraScreenTile(this);
                    updateExtraScreensCount();
                });

                updateExtraScreenTile(checkbox);
            });

            function renderInheritedScreens(links) {
                if (!previewBox) return;

                if (!links.length) {
                    previewBox.innerHTML = '<span class="text-sm text-secondary-light">This category has no screens assigned yet.</span>';
                    return;
                }

                previewBox.innerHTML = links.map(function (link) {
                    return '<span class="screen-badge">' + link.link_name + '</span>';
                }).join('');
            }

            function renderSavedInheritedScreens() {
                renderInheritedScreens(savedAccessLinks);
            }

            function applySavedAccessState() {
                renderSavedInheritedScreens();
                syncExtraCheckboxes([], false);
                applySavedAccessLinks();
            }

            function loadCategoryScreens(categoryId, updateChecks) {
                if (!previewBox || (accessToggle && !accessToggle.checked)) return;

                if (hasSavedAccessLinks) {
                    applySavedAccessState();
                    return;
                }

                if (!categoryId) {
                    previewBox.innerHTML = '<span class="text-sm text-secondary-light">Select a user category to preview inherited screens.</span>';
                    syncExtraCheckboxes([], updateChecks);
                    return;
                }

                fetch(categoryLinksUrl + '/' + categoryId)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        const links = data.links || [];
                        const inheritedLinkIds = links.map(function (link) { return parseInt(link.link_id, 10); });
                        renderInheritedScreens(links);
                        syncExtraCheckboxes(inheritedLinkIds, updateChecks);
                    })
                    .catch(function () {
                        previewBox.innerHTML = '<span class="text-sm text-danger-600">Could not load category screens.</span>';
                    });
            }

            function toggleSystemAccess() {
                if (!accessToggle || !accessFields) return;

                const enabled = accessToggle.checked;
                accessFields.classList.toggle('is-disabled', !enabled);

                if (!enabled) {
                    if (categorySelect) categorySelect.value = '';
                    extraCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                        delete checkbox.dataset.categorySelected;
                        updateExtraScreenTile(checkbox);
                    });
                    updateExtraScreensCount();

                    if (previewBox) {
                        previewBox.innerHTML = '<span class="text-sm text-secondary-light">System login is disabled for this staff member.</span>';
                    }
                } else if (hasSavedAccessLinks) {
                    applySavedAccessState();
                } else if (categorySelect && categorySelect.value) {
                    loadCategoryScreens(categorySelect.value, true);
                }
            }

            if (hasSavedAccessLinks) {
                applySavedAccessState();
            } else {
                updateExtraScreensCount();
            }

            if (accessToggle) {
                accessToggle.addEventListener('change', toggleSystemAccess);
                toggleSystemAccess();
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', function () {
                    loadCategoryScreens(this.value, !hasSavedAccessLinks);
                });

                if (!hasSavedAccessLinks && accessToggle && accessToggle.checked && categorySelect.value) {
                    loadCategoryScreens(categorySelect.value, true);
                }
            }
        };
    })();
</script>
