// Global helper for inline onchange (if used inline in HTML)
function showLanguageLevelSelection(selectElem) {
  const container = document.getElementById('languageLevelContainer');
  if (!container) return;
  container.classList.toggle('d-none', !selectElem.value);
}
   // Function to update the character count for a given input field
   function updateCharacterCount(input, counter, maxLength) {
    const currentLength = input.value.length;
    counter.textContent = `${currentLength} / ${maxLength}`;

    // Add validation for input length
    if (currentLength > maxLength) {
      input.setCustomValidity('Character limit exceeded');
    } else {
      input.setCustomValidity('');
    }
  }

document.addEventListener('DOMContentLoaded', () => {
  const skillSearchInput = document.getElementById('skillSearchInput');
  const skillSearchResults = document.getElementById('skillSearchResults');
  const selectedSkillsFlow = document.getElementById('selectedSkillsFlow');
  const hiddenSkills = document.getElementById('hiddenSkills');
  const clearSkillSearchBtn = document.getElementById('clearSkillSearchBtn');
 

 // Select all input or textarea fields with ids ending in "Field"
  let selectedSkills = [];
  let debounceTimeout;

  const formFields = document.querySelectorAll('input[id$="Field"], textarea[id$="Field"]');
  console.log(formFields)
  
  // Loop through all form fields to set up event listeners
  formFields.forEach(input => {
    const counterId = input.id + "Counter"; // Get the corresponding counter id (e.g., cvTitleFieldCounter)
    const counter = document.getElementById(counterId);

    if (counter) {
      const maxLength = input.getAttribute('maxlength'); // Get the maxlength from the input field
      updateCharacterCount(input, counter, maxLength); // Initialize with current length

      // Add event listener to update the counter as the user types
      input.addEventListener('input', function() {
        updateCharacterCount(input, counter, maxLength);
      });
    }
  });


  function updateHiddenSkills() {
    hiddenSkills.innerHTML = ''; // Clear previous hidden inputs
    selectedSkills.forEach((skill, index) => {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = `cv[skills]`; // Send the skills as a string
      hiddenInput.value = selectedSkills.join(','); // Store as a comma-separated string
      hiddenSkills.appendChild(hiddenInput);
    });
  }

  async function fetchSkills(query) {
    try {
        const response = await fetch(`/cv/skills/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        displaySkillResults(data);
    } catch (error) {
        console.error('Error fetching skills:', error);
        showMessage("Error fetching skills. Please try again.");
    }
}


function displaySkillResults(skills) {
  skillSearchResults.innerHTML = '';
  if (skills.length === 0) {
      showMessage("No skills found");
      return;
  }

  skills.forEach(skill => {
      const item = document.createElement('div');
      item.className = 'search-result-item';
      item.textContent = skill.name;
      item.addEventListener('click', () => {
          addSkillTag(skill.name);
          skillSearchInput.value = '';  // Clear input after selection
          skillSearchResults.innerHTML = '';  // Clear results
      });
      skillSearchResults.appendChild(item);
  });
}

  function showMessage(text) {
    const div = document.createElement('div');
    div.className = 'no-results';
    div.textContent = text;
    skillSearchResults.innerHTML = '';
    skillSearchResults.appendChild(div);
  }

 
// This function adds the selected skill as a tag and a hidden input
function addSkillTag(skill) {
  if (selectedSkills.includes(skill)) return; // Prevent duplicates

  selectedSkills.push(skill);

  // Create a container for the skill tag with a close button
  const tagContainer = document.createElement('div');
  tagContainer.className = 'skill-tag-container';

  // Create the tag (badge) element
  const tag = document.createElement('span');
  tag.className = 'badge bg-primary text-white';
  tag.textContent = skill;

  // Create the 'X' button for removal
  const closeButton = document.createElement('button');
  closeButton.className = 'btn-close';
  closeButton.type = 'button';

  // Add event listener to the 'X' button to remove the tag
  closeButton.addEventListener('click', () => {
      selectedSkills = selectedSkills.filter(s => s !== skill);
      selectedSkillsFlow.removeChild(tagContainer); // Remove the tag container
      hiddenSkills.removeChild(hiddenInput); // Remove the hidden input
      updateHiddenSkills();
  });

  // Append the close button and the tag to the tag container
  tagContainer.appendChild(tag);
  tagContainer.appendChild(closeButton);

  updateHiddenSkills()

  // Append the tag container to the selected skills flow
  selectedSkillsFlow.appendChild(tagContainer);
}

// Add event listeners for search input and clearing the search
skillSearchInput.addEventListener('input', () => {
  const query = skillSearchInput.value.trim();
  clearTimeout(debounceTimeout);
  if (query) {
      debounceTimeout = setTimeout(() => fetchSkills(query), 1000);  // Fetch after 1 second of no input
  } else {
      skillSearchResults.innerHTML = '';  // Clear search results
  }
});

clearSkillSearchBtn.addEventListener('click', () => {
  skillSearchInput.value = '';
  skillSearchResults.innerHTML = '';  // Clear results
});
  

  // ─── Utility Functions ─────────────────────────────
  const show = id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
  };
  const hide = id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  };
  const levelName = lvl => ['Beginner', 'Novice', 'Intermediate', 'Advanced', 'Expert'][lvl - 1] || '';

  // ─── EXPERIENCE SECTION ─────────────────────────────
  let experienceCount = 0;
  const addExpBtn   = document.getElementById('addExperienceBtn');
  const closeExpBtn = document.getElementById('closeExperienceBtn');
  const confirmExp  = document.getElementById('confirmExperienceBtn');

  addExpBtn && addExpBtn.addEventListener('click', () => show('experienceModal'));
  closeExpBtn && closeExpBtn.addEventListener('click', () => hide('experienceModal'));
  confirmExp && confirmExp.addEventListener('click', addExperience);

  function addExperience() {
    const data = {
      type: document.getElementById("experienceType").value,
      position: document.getElementById("positionField").value,
      location: document.getElementById("locationField").value,
      description: document.getElementById("descriptionField").value,
      startDate: document.getElementById("startDatePicker").value,
      endDate: document.getElementById("endDatePicker").value
    };

    // Append to the left-hand experience container
    const entry = document.createElement('div');
    entry.className = 'mb-2 p-2 border';
    entry.innerHTML = `<strong>${data.type}:</strong> ${data.position} at ${data.location} (${data.startDate} → ${data.endDate})<br>${data.description}`;
    document.getElementById('experienceContainer').appendChild(entry);

    // Add preview card
    addExperiencePreview(data);

    // Append hidden inputs for form submission
    appendHiddenInputs('hiddenExperiences', data, experienceCount, 'experiences');
    experienceCount++;

    hide('experienceModal');
    document.getElementById('experienceForm').reset();
  }

  function addExperiencePreview(data) {
    const container = document.getElementById('experiencePreviewContainer');
    if (!container) return;
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';
    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title">${data.position}</h5>
        <h6 class="card-subtitle mb-2 text-muted">${data.location}</h6>
        <dl class="row mb-0">
          <dt class="col-sm-4">Type</dt><dd class="col-sm-8">${data.type}</dd>
          <dt class="col-sm-4">Duration</dt><dd class="col-sm-8">${data.startDate} → ${data.endDate}</dd>
          <dt class="col-sm-4">Description</dt><dd class="col-sm-8">${data.description}</dd>
        </dl>
      </div>`;
    container.appendChild(card);
  }

  // ─── CERTIFICATE SECTION ───────────────────────────
  let certificateCount = 0;
  const addCertBtn   = document.getElementById('addCertificateBtn');
  const closeCertBtn = document.getElementById('closeCertificateBtn');
  const confirmCert  = document.getElementById('confirmCertificateBtn');

  addCertBtn && addCertBtn.addEventListener('click', () => show('certificateModal'));
  closeCertBtn && closeCertBtn.addEventListener('click', () => hide('certificateModal'));
  confirmCert && confirmCert.addEventListener('click', addCertificate);

  function addCertificate() {
    const data = {
      name: document.getElementById("certificateNameField").value.trim(),
      association: document.getElementById("certificateAssociationField").value.trim(),
      description: document.getElementById("certificateDescriptionField").value.trim(),
      date: document.getElementById("certificateDatePicker").value,
      media: document.getElementById("certificateMediaLabel").textContent
    };

    // Append to left-hand certificate container
    const entry = document.createElement('div');
    entry.className = 'mb-2 p-2 border';
    entry.innerHTML = `<strong>${data.name}</strong><br>Issued by: ${data.association}<br>Date: ${data.date}<br>${data.description}`;
    document.getElementById('certificateContainer').appendChild(entry);

    // Add preview card
    addCertificatePreview(data);

    // Append hidden inputs for form submission
    appendHiddenInputs('hiddenCertificates', data, certificateCount, 'certificates');
    certificateCount++;

    hide('certificateModal');
    document.getElementById('certificateForm').reset();
    document.getElementById('certificateMediaLabel').textContent = 'No file selected';
  }

  function addCertificatePreview({ name, association, description, date, media }) {
    const container = document.getElementById('certificatesPreviewContainer');
    if (!container) return;
    const card = document.createElement('div');
    card.className = 'card mb-3 shadow-sm';
    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title">${name}</h5>
        <h6 class="card-subtitle mb-2 text-muted">Issued by: ${association}</h6>
        <dl class="row mb-0">
          <dt class="col-sm-4">Date</dt><dd class="col-sm-8">${date}</dd>
          <dt class="col-sm-4">Description</dt><dd class="col-sm-8">${description}</dd>
          ${ media ? `<dt class="col-sm-4">File</dt>
                     <dd class="col-sm-8"><a href="/uploads/${media}" target="_blank">${media}</a></dd>` : '' }
        </dl>
      </div>`;
    container.appendChild(card);
  }

  // ─── DOWNLOAD SECTION ──────────────────────────────
  const downloadBtn     = document.getElementById('downloadBtn');
  const closeDownload   = document.getElementById('closeDownloadBtn');
  const confirmDownload = document.getElementById('confirmDownloadBtn');

  downloadBtn && downloadBtn.addEventListener('click', () => show('downloadModal'));
  closeDownload && closeDownload.addEventListener('click', () => hide('downloadModal'));
  confirmDownload && confirmDownload.addEventListener('click', () => {
    downloadCV();
    hide('downloadModal');
  });

  // ─── LANGUAGE SECTION (Inline, No Modal) ───────────
  const addLangBtn      = document.getElementById('addLanguageBtn');
  const languageDropdown = document.getElementById('languagesDropdown');
  const languageError   = document.getElementById('languageErrorLabel');
  const langLevelContainer = document.getElementById('languageLevelContainer');
  const langLevelError  = document.getElementById('levelErrorLabel');
  const langDots        = document.querySelectorAll('#levelDots span');
  let selectedLevelLang = 0;  // Local variable for language proficiency

  languageDropdown.addEventListener('change', () => {
    languageError.classList.add('d-none');
    if (languageDropdown.value) {
      langLevelContainer.classList.remove('d-none');
    } else {
      langLevelContainer.classList.add('d-none');
    }
    selectedLevelLang = 0;
    langDots.forEach(dot => {
      dot.textContent = '○';
      dot.classList.remove('text-primary');
    });
  });

  langDots.forEach(dot => {
    dot.addEventListener('click', () => {
      selectedLevelLang = parseInt(dot.dataset.level, 10);
      langLevelError.classList.add('d-none');
      langDots.forEach(d => {
        const lvl = parseInt(d.dataset.level, 10);
        d.textContent = (lvl <= selectedLevelLang) ? '●' : '○';
        d.classList.toggle('text-primary', lvl <= selectedLevelLang);
      });
    });
  });

  addLangBtn.addEventListener('click', () => {
    const lang = languageDropdown.value;
    if (!lang) {
      languageError.classList.remove('d-none');
      return;
    }
    if (!selectedLevelLang) {
      langLevelError.classList.remove('d-none');
      return;
    }
    addLanguageBox(lang, selectedLevelLang);
    // Reset controls
    languageDropdown.value = '';
    langLevelContainer.classList.add('d-none');
    langDots.forEach(dot => {
      dot.textContent = '○';
      dot.classList.remove('text-primary');
    });
    selectedLevelLang = 0;
  });

  function addLanguageBox(language, level) {
    const preview = document.getElementById('languageContainer');
    const hidden = document.getElementById('hiddenLanguages');
    const index = preview.children.length;
  
    const box = document.createElement('div');
    box.className = 'alert alert-secondary d-flex align-items-center justify-content-between';
    box.innerHTML = `
      <div><strong>${language}</strong> — ${levelName(level)}</div>
      <button type="button" class="btn-close" aria-label="Remove"></button>
    `;
  
    // Remove the language from the dropdown
    const dropdownOption = document.querySelector(`#languagesDropdown option[value="${language}"]`);
    if (dropdownOption) {
      dropdownOption.style.display = 'none'; // Hides the selected language in the dropdown
    }
  
    // Hidden inputs for form submission
    ['name', 'level'].forEach(key => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `languages[${index}][${key}]`;
      input.value = key === 'name' ? language : levelName(level);
      hidden.appendChild(input);
    });
  
    box.querySelector('.btn-close').addEventListener('click', () => {
      preview.removeChild(box);
      Array.from(hidden.querySelectorAll(`input[name^="languages[${index}]"]`))
        .forEach(input => hidden.removeChild(input));
  
      // Re-add the language back to the dropdown
      const option = document.createElement('option');
      option.value = language;
      option.textContent = language;
      document.getElementById('languagesDropdown').appendChild(option);
  
      // Sort the dropdown options alphabetically
      sortDropdownOptions();
    });
  
    preview.appendChild(box);
  }
// Sort dropdown options alphabetically
function sortDropdownOptions() {
  const dropdown = document.getElementById('languagesDropdown');
  const options = Array.from(dropdown.options);
  options.sort((a, b) => a.textContent.localeCompare(b.textContent));
  options.forEach(option => dropdown.appendChild(option));
}
  
});
