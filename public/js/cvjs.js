// at top of cvjs.js
window.selectedSkills = window.selectedSkills || [];
window.currentExperiences = window.currentExperiences || [];
window.currentCertificates = window.currentCertificates || [];
window.currentLanguages = window.currentLanguages || [];
function sentenceCase(str) {
  // Capitalize first char of the string, and any char after . ! or ?
  return str.replace(
    /(^\s*|[\.!?]\s+)([a-z])/g,
    (match, lead, chr) => lead + chr.toUpperCase()
  );
}
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
// Function to send a request to check grammar
function checkGrammar(text, field) {
  console.log('checkGrammar called with text:', text);  // Log to verify it's called
  fetch('/cv/grammar-check', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ text: text }),
  })
    .then(response => response.json())
    .then(data => {
      console.log('Grammar Check Response:', data);

      // If the response is empty, no issues were found
      if (!data || data.length === 0) {
        console.log('No corrections needed');
        return;
      }

      highlightField(field);  // Highlight the entire field
      displayContextMenu(data, field);  // Show context menu with suggestions
    })
    .catch(error => {
      console.error('Error checking grammar:', error);
    });
}

// Function to highlight the entire input field
function highlightField(field) {
  // Add a red border to highlight the input field
  field.style.border = '2px solid red';  // Example of highlighting the input field with a red border
  field.style.backgroundColor = 'rgba(255, 0, 0, 0.1)';  // Optional: Change background color to indicate error
}

// Function to remove the highlight from the input field
function removeHighlight(field) {
  // Remove the red border and background color to return to normal
  field.style.border = '';
  field.style.backgroundColor = '';
}

// Function to display the context menu with grammar suggestions
function displayContextMenu(corrections, field) {
  const appliedCorrections = new Set(); // Keep track of applied corrections

  // Add event listener for right-click (contextmenu) on the field
  field.addEventListener('contextmenu', function (event) {
    event.preventDefault(); // Prevent the default context menu

    // Create the custom context menu
    const contextMenu = document.createElement('div');
    contextMenu.style.position = 'absolute';
    contextMenu.style.left = `${event.pageX}px`;
    contextMenu.style.top = `${event.pageY}px`;
    contextMenu.style.padding = '8px';
    contextMenu.style.backgroundColor = '#fff';
    contextMenu.style.border = '1px solid #ccc';
    contextMenu.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
    contextMenu.style.zIndex = '9999';

    corrections.forEach((correction, index) => {
      // Skip suggestions that have already been applied
      if (appliedCorrections.has(index)) return;

      const suggestionText = document.createElement('p');
      suggestionText.textContent = `Replace "${correction.incorrect}" with "${correction.suggestion}"`;
      suggestionText.style.cursor = 'pointer';

      suggestionText.addEventListener('click', function () {
        // Apply the correction (replace the word in the field)
        applyCorrection(correction, field);

        // Mark this correction as applied
        appliedCorrections.add(index);

        // Remove the suggestion from the menu once applied
        contextMenu.removeChild(suggestionText);

        // If all corrections are applied, remove the highlight
        if (appliedCorrections.size === corrections.length) {
          removeHighlight(field);  // Remove highlight after all corrections are applied
        }
      });

      contextMenu.appendChild(suggestionText);
    });

    // Append the context menu to the document body
    document.body.appendChild(contextMenu);

    // Close the context menu when clicking anywhere else
    document.addEventListener('click', function () {
      contextMenu.remove();
    }, { once: true });
  });
}

// Function to apply the correction (replace the incorrect word with the suggestion)
function applyCorrection(correction, field) {
  const start = correction.offset;
  const end = start + correction.length;
  const suggestion = correction.suggestion;

  // Get the current value in the field (textarea or input)
  const currentValue = field.value;
  const correctedText = currentValue.substring(0, start) + suggestion + currentValue.substring(end);

  // Update the field with the corrected text
  field.value = correctedText;

  // Optionally, you can re-highlight the field again after applying the correction
  highlightField(field);
}




// Validate file extension and update UI if valid.
function validateCertificateForm() {
  // Get certificate form fields.
  const nameElem = document.getElementById('certificateNameField');
  const associationElem = document.getElementById('certificateAssociationField');
  const descriptionElem = document.getElementById('certificateDescriptionField');
  const dateElem = document.getElementById('certificateDatePicker');

  // Get error label elements.
  const nameError = document.getElementById('certificateNameErrorLabel');
  const associationError = document.getElementById('certificateAssociationErrorLabel');
  const descriptionError = document.getElementById('certificateDescriptionErrorLabel');
  const dateError = document.getElementById('certificateDateErrorLabel');

  // Reset all error messages.
  [nameError, associationError, descriptionError, dateError].forEach(el => el.classList.add('d-none'));

  let isValid = true;

  // Validate that all required fields are not empty.
  if (!nameElem.value.trim()) {
    nameError.textContent = 'Certificate name cannot be empty!';
    nameError.classList.remove('d-none');
    isValid = false;
  }
  if (!associationElem.value.trim()) {
    associationError.textContent = 'Issuing organization cannot be empty!';
    associationError.classList.remove('d-none');
    isValid = false;
  }
  if (!descriptionElem.value.trim()) {
    descriptionError.textContent = 'Certificate description cannot be empty!';
    descriptionError.classList.remove('d-none');
    isValid = false;
  }

  // Validate issue date.
  if (!dateElem.value) {
    dateError.textContent = 'Issue date is required!';
    dateError.classList.remove('d-none');
    isValid = false;
  } else {
    const issueDate = new Date(dateElem.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (issueDate > today) {
      dateError.textContent = 'Issue date cannot be in the future.';
      dateError.classList.remove('d-none');
      isValid = false;
    }
  }

  return isValid;
}



function validateAndSetCertificateFile(file) {
  const allowedExtensions = ['png', 'jpg', 'doc', 'pdf'];
  const fileName = file.name;
  const fileExtension = fileName.split('.').pop().toLowerCase();

  const uploadErrorElem = document.getElementById('certificateUploadError');
  // Check if the file extension is allowed.
  if (allowedExtensions.indexOf(fileExtension) === -1) {
    uploadErrorElem.textContent = 'Invalid file type. Only PNG, JPG, DOC, or PDF files are allowed.';
    uploadErrorElem.classList.remove('d-none');
    return false;
  }

  // Clear any previous error
  uploadErrorElem.textContent = '';
  uploadErrorElem.classList.add('d-none');

  // Update the label to show the file name.
  const certificateMediaLabel = document.getElementById('certificateMediaLabel');
  certificateMediaLabel.textContent = fileName;

  // Optionally, save the file reference or trigger upload logic here.
  return true;
}

// Handler for dragover event to allow drop.
function handleDragOver(event) {
  event.preventDefault();
  event.dataTransfer.dropEffect = "copy";
}

// Handler for drop event on the upload box.
function handleDragDropped(event) {
  event.preventDefault();
  const files = event.dataTransfer.files;
  if (files.length) {
    validateAndSetCertificateFile(files[0]);
  }
}

function validateExperienceForm() {
  // Retrieve elements for all fields
  const typeElem = document.getElementById('experienceType');
  const positionElem = document.getElementById('positionField');
  const locationElem = document.getElementById('locationField');
  const descriptionElem = document.getElementById('descriptionField');
  const startDateElem = document.getElementById('startDatePicker');
  const endDateElem = document.getElementById('endDatePicker');

  // Retrieve error label elements
  const typeError = document.getElementById('typeErrorLabel');
  const positionError = document.getElementById('positionErrorLabel');
  const locationError = document.getElementById('locationErrorLabel');
  const descriptionError = document.getElementById('descritpionErrorLabel');
  const startDateError = document.getElementById('startDateErrorLabel');
  const endDateError = document.getElementById('endDateErrorLabel');
  const durationError = document.getElementById('durationErrorLabel');

  // Reset all error messages by adding the d-none class
  [typeError, positionError, locationError, descriptionError, startDateError, endDateError, durationError].forEach(el => el.classList.add('d-none'));

  let isValid = true;

  // 1. Validate Experience Type - must not be empty and allowed value
  const typeValue = typeElem.value.trim();
  if (!typeValue || (typeValue !== 'Academic' && typeValue !== 'Internship')) {
    typeError.textContent = 'Experience type must be Academic or Internship.';
    typeError.classList.remove('d-none');
    isValid = false;
  }

  // 2. Validate other required text fields (position, location, description)
  if (!positionElem.value.trim()) {
    positionError.textContent = 'Position cannot be empty!';
    positionError.classList.remove('d-none');
    isValid = false;
  }

  if (!locationElem.value.trim()) {
    locationError.textContent = 'Location cannot be empty!';
    locationError.classList.remove('d-none');
    isValid = false;
  }

  if (!descriptionElem.value.trim()) {
    descriptionError.textContent = 'Description cannot be empty!';
    descriptionError.classList.remove('d-none');
    isValid = false;
  }

  // 3. Validate the dates  
  const startDateStr = startDateElem.value;
  const endDateStr = endDateElem.value;

  if (!startDateStr) {
    startDateError.textContent = 'Start date is required!';
    startDateError.classList.remove('d-none');
    isValid = false;
  }
  if (!endDateStr) {
    endDateError.textContent = 'End date is required!';
    endDateError.classList.remove('d-none');
    isValid = false;
  }

  // If either date is missing, we cannot check further date validations.
  if (startDateStr && endDateStr) {
    const startDate = new Date(startDateStr);
    const endDate = new Date(endDateStr);

    const minStartDate = new Date('1925-01-01');
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Normalize today's date

    // Start date must not be before January 1, 1925.
    if (startDate < minStartDate) {
      startDateError.textContent = 'Start date cannot be before January 1, 1925.';
      startDateError.classList.remove('d-none');
      isValid = false;
    }

    // End date must not be in the future (beyond today).
    if (endDate > today) {
      endDateError.textContent = 'End date cannot be in the future.';
      endDateError.classList.remove('d-none');
      isValid = false;
    }

    // Duration must be at least 7 days.
    const dayDiff = (endDate - startDate) / (1000 * 3600 * 24);
    if (dayDiff < 7) {
      durationError.textContent = 'Duration must be at least 7 days.';
      durationError.classList.remove('d-none');
      isValid = false;
    }
  }

  return isValid;
}

document.addEventListener('DOMContentLoaded', () => {
  const hiddenInput = document.querySelector('input[name="cv[skills]"]');
  const skillSearchInput = document.getElementById('skillSearchInput');
  const skillSearchResults = document.getElementById('skillSearchResults');
  const selectedSkillsFlow = document.getElementById('selectedSkillsFlow');
  const clearSkillSearchBtn = document.getElementById('clearSkillSearchBtn');
  // ---- seed your counters from what's already in the DOM ----
  const expContainer = document.getElementById('experienceContainer');
  const certContainer = document.getElementById('certificateContainer');
  // start the counters at the current number of items
  let experienceCount = expContainer ? expContainer.children.length : 0;
  let certificateCount = certContainer ? certContainer.children.length : 0;
  // Select all input or textarea fields with ids ending in "Field"

  let debounceTimeout;
  const sentenceFields = document.querySelectorAll(
    'input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=file]):not([type=button]):not([type=submit]), textarea'
  );
  console.log(sentenceFields);
  sentenceFields.forEach(field => {
    field.addEventListener('input', () => {
      const newValue = sentenceCase(field.value);
      console.log(newValue);
      if (newValue !== field.value) {
        field.value = newValue;
      }
    });
  });

  const fields = document.querySelectorAll('input[maxlength], textarea[maxlength]');
  if (hiddenInput && hiddenInput.value.trim()) {
    hiddenInput.value.split(',')
      .map(s => s.trim())
      .filter(s => s)
      .forEach(s => addSkillTag(s));
  }

  fields.forEach(field => {
    const maxLength = field.getAttribute('maxlength');

    // Create a small element to display the character count.
    const counter = document.createElement('small');
    counter.className = 'character-counter';
    counter.style.display = 'block';
    counter.style.marginTop = '0.5rem';

    // Insert the counter element immediately after the field.
    field.parentNode.insertBefore(counter, field.nextSibling);

    // Function to update the counter text and validation.
    const updateCounter = () => {
      const currentLength = field.value.length;
      counter.textContent = `${currentLength} / ${maxLength}`;

      if (currentLength > maxLength) {
        field.setCustomValidity('Character limit exceeded');
        counter.style.color = 'red';
      } else {
        field.setCustomValidity('');
        counter.style.color = '';
      }
    };

    // Initial update.
    updateCounter();

    // Update counter on input events.
    field.addEventListener('input', () => {

      updateCounter();
      const text = field.value.trim();

      // Skip if the field is empty
      if (text.length === 0) return;

      // Clear the previous timeout
      clearTimeout(debounceTimeout);

      // Set a new timeout for 1 second
      debounceTimeout = setTimeout(() => {
        // Send the text to the backend to check grammar
        checkGrammar(text, field);
      }, 1000); // 1000 ms = 1 second
    });
  });
  // Create or get a hidden file input for certificate file upload.
  let hiddenFileInput = document.getElementById('certificateFileInput');
  if (!hiddenFileInput) {
    hiddenFileInput = document.createElement('input');
    hiddenFileInput.type = 'file';
    hiddenFileInput.id = 'certificateFileInput';
    hiddenFileInput.style.display = 'none';
    // Limit accepted file types.
    hiddenFileInput.accept = '.png, .jpg, .doc, .pdf';
    document.body.appendChild(hiddenFileInput);
  }

  // When a file is selected via the file input, validate it.
  hiddenFileInput.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (file) {
      validateAndSetCertificateFile(file);
    }
  });

  // Find the Browse button inside the upload box and trigger the file input on click.
  const browseButton = document.querySelector('#uploadBox button');
  if (browseButton) {
    browseButton.addEventListener('click', () => {
      hiddenFileInput.click();
    });
  }

  // Attach dragover and drop event handlers to the upload box.
  const uploadBox = document.getElementById('uploadBox');
  if (uploadBox) {
    uploadBox.addEventListener('dragover', handleDragOver);
    uploadBox.addEventListener('drop', handleDragDropped);
  }

  function updateHiddenSkills() {
    if (hiddenInput) {
      hiddenInput.value = selectedSkills.join(',');
    }
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
        console.log('Hidden skills now:', hiddenInput.value);
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



  function addSkillTag(skill) {
    if (selectedSkills.includes(skill)) return;     // no duplicates
    window.selectedSkills.push(skill);               // track

    // re‐sync hidden input
    hiddenInput.value = window.selectedSkills.join(',');

    // build the badge+×
    const badge = document.createElement('span');
    badge.className = 'badge bg-primary text-white me-2 mb-2 d-inline-flex align-items-center';
    badge.textContent = skill;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close btn-close-white btn-sm ms-2';
    btn.setAttribute('aria-label', 'Remove');
    btn.addEventListener('click', () => {
      // remove from array
      window.selectedSkills = window.selectedSkills.filter(s => s !== skill);
      // update hidden
      hiddenInput.value = selectedSkills.join(',');
      // remove badge
      badge.remove();
      window.updateSkillsSection();
    });

    badge.appendChild(btn);
    selectedSkillsFlow.appendChild(badge);
    window.updateSkillsSection();
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
    if (el) el.classList.add('show');
  };
  const hide = id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
  };
  const levelName = lvl => ['Beginner', 'Novice', 'Intermediate', 'Advanced', 'Expert'][lvl - 1] || '';

  // ─── EXPERIENCE SECTION ─────────────────────────────

  const addExpBtn = document.getElementById('addExperienceBtn');
  const closeExpBtn = document.getElementById('closeExperienceBtn');
  const confirmExp = document.getElementById('confirmExperienceBtn');

  addExpBtn && addExpBtn.addEventListener('click', () => show('experienceModal'));
  closeExpBtn && closeExpBtn.addEventListener('click', () => hide('experienceModal'));
  confirmExp && confirmExp.addEventListener('click', () => {
    if (validateExperienceForm()) {
      // Proceed with adding the experience if validation passed
      addExperience();
    } else {
      console.log('Experience form validation failed.');
    }
  });

  // Attach a click listener to the container for experience entries.
  document
  .getElementById('experienceContainer')
  .addEventListener('click', e => {
    // 1) only proceed if they clicked one of the × buttons
    const btn = e.target.closest('button.remove-experience');
    if (!btn) return;

    // 2) find which index in the array they clicked
    const idx = Number(btn.dataset.index);

    // 3) remove that item from your in-memory model
    window.currentExperiences.splice(idx, 1);

    // 4) clear out BOTH the visible cards AND the hidden inputs
    const container = document.getElementById('experienceContainer');
    const hidden    = document.getElementById('hiddenExperiences');
    container.innerHTML = '';
    hidden.innerHTML    = '';

    // 5) re-render everything from window.currentExperiences
    window.currentExperiences.forEach((exp, i) => {
      // --- build the “card” just like your addExperience() does ---
      const entry = document.createElement('div');
      entry.dataset.index = i;
      entry.className = [
        "d-flex","flex-column",
        "bg-secondary","bg-opacity-75",
        "text-white","rounded",
        "px-3","py-2","mb-2",
        "shadow-sm"
      ].join(" ");

      entry.innerHTML = `
        <div class="d-flex justify-content-between align-items-start w-100">
          <div class="flex-grow-1">
            <div class="edu-main">
              <div>
                <h5 class="edu-degree">${exp.position}</h5>
                <div class="edu-institution">${exp.location}</div>
              </div>
              <div class="edu-meta">
                ${exp.description ? `<div class="edu-honor">${exp.description}</div>` : ""}
                <div class="edu-year">${exp.startDate} — ${exp.endDate}</div>
              </div>
            </div>
          </div>
          <button
            type="button"
            class="btn p-1 ms-2 remove-experience"
            data-index="${i}"
            aria-label="Remove"
          >
            <span class="text-danger fs-3">&times;</span>
          </button>
        </div>
      `;
      container.appendChild(entry);

      // --- mirror into hidden inputs ---
      ['type','position','location','description','startDate','endDate']
        .forEach(key => {
          const input = document.createElement('input');
          input.type  = 'hidden';
          input.name  = `experiences[${i}][${key}]`;
          input.value = exp[key];
          hidden.appendChild(input);
        });
    });

    // 6) finally, update the CV‐preview panels
    window.updateExperienceSection && window.updateExperienceSection();
    window.updateEducationSection  && window.updateEducationSection();
  });

 function addExperience() {
    const data = {
      type: document.getElementById("experienceType").value,
      position: document.getElementById("positionField").value,
      location: document.getElementById("locationField").value,
      description: document.getElementById("descriptionField").value,
      startDate: document.getElementById("startDatePicker").value,
      endDate: document.getElementById("endDatePicker").value
    };
    console.log()
    window.currentExperiences.push(data);
    // Append to the left-hand experience container
    // Store the current index (experienceCount) for this entry.
    const experienceIndex = experienceCount;
    // Create a container for the entry with a data-index attribute.
    const entry = document.createElement('div');
    entry.dataset.index = experienceIndex;
    entry.className = [
      "d-flex",
      "flex-column",
      "bg-secondary", "bg-opacity-75",
      "text-white",
      "rounded",
      "px-3", "py-2", "mb-2",
      "shadow-sm"
    ].join(" ");

    entry.innerHTML = `
    <div class="d-flex justify-content-between align-items-start w-100">
      <div class="flex-grow-1">
        <div class="edu-main">
          <div>
            <h5 class="edu-degree">${data.position}</h5>
            <div class="edu-institution">${data.location}</div>
          </div>
          <div class="edu-meta">
            ${data.description ? `<div class="edu-honor">${data.description}</div>` : ""}
            <div class="edu-year">${data.startDate} — ${data.endDate}</div>
          </div>
        </div>
      </div>
   <button 
  type="button" 
  class="btn p-1 ms-2 remove-experience" 
  data-index="${experienceIndex}" 
  aria-label="Remove"
>
  <span class="text-danger fs-3">&times;</span>
</button>
    </div>
  `;

    document.getElementById('experienceContainer').appendChild(entry);



    const container = document.getElementById('hiddenExperiences');
    ['type', 'position', 'location', 'description', 'startDate', 'endDate'].forEach(key => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `experiences[${experienceCount}][${key}]`;
      input.value = data[key];
      container.appendChild(input);
    });
    experienceCount++;

    hide('experienceModal');
    document.getElementById('experienceForm').reset();
    window.updateExperienceSection();
    window.updateEducationSection();
  }



  // ─── CERTIFICATE SECTION ───────────────────────────
  const addCertBtn = document.getElementById('addCertificateBtn');
  const closeCertBtn = document.getElementById('closeCertificateBtn');
  const confirmCert = document.getElementById('confirmCertificateBtn');

  addCertBtn && addCertBtn.addEventListener('click', () => show('certificateModal'));
  closeCertBtn && closeCertBtn.addEventListener('click', () => hide('certificateModal'));
  confirmCert && confirmCert.addEventListener('click', () => {
    if (validateCertificateForm()) {
      // All certificate fields validated; proceed with adding the certificate.
      addCertificate();
    } else {
      console.log('Certificate form validation failed.');
    }
  });

  function addCertificate() {
    const data = {
      name: document.getElementById("certificateNameField").value.trim(),
      association: document.getElementById("certificateAssociationField").value.trim(),
      description: document.getElementById("certificateDescriptionField").value.trim(),
      date: document.getElementById("certificateDatePicker").value,
      media: document.getElementById("certificateMediaLabel").textContent
    };
    window.currentCertificates.push(data);
    const idx = certificateCount++;
  
    // create the box
    const box = document.createElement('div');
    box.dataset.index = idx;
    box.className = [
      'd-flex',
      'flex-column',
      'bg-secondary',      // your secondary
      'bg-opacity-75',     // darker
      'text-white',
      'rounded',
      'px-3','py-2','mb-2',
      'shadow-sm'
    ].join(' ');
  
    box.innerHTML = `
      <div class="d-flex justify-content-between align-items-start w-100">
        <div class="flex-grow-1">
          <div class="cert-main">
            <div>
              <div class="cert-title">${data.name}</div>
              <div class="cert-issuer">Issued by: ${data.association}</div>
            </div>
            <div class="cert-meta">
              <div class="cert-date">${data.date}</div>
            </div>
          </div>
          ${data.description
            ? `<ul class="cert-bullets"><li>${data.description}</li></ul>`
            : ''}
        </div>
        <button
          type="button"
          class="btn p-1 ms-2 remove-certificate text-danger fs-3"
          data-index="${idx}"
          aria-label="Remove"
        >
          <span class="text-danger fs-3">&times;</span>
        </button>
      </div>
    `;
  
    // append to DOM
    document.getElementById('certificateContainer').appendChild(box);
  
    // mirror into hidden inputs
    const hidden = document.getElementById('hiddenCertificates');
    ['name','association','description','date','media'].forEach(key => {
      const i = document.createElement('input');
      i.type  = 'hidden';
      i.name  = `certificates[${idx}][${key}]`;
      i.value = data[key];
      hidden.appendChild(i);
    });
  
    // tear down
    hide('certificateModal');
    document.getElementById('certificateForm').reset();
    document.getElementById('certificateMediaLabel').textContent = 'No file selected';
    window.updateCertificatesSection();
  }
  


  // ─── DOWNLOAD SECTION ──────────────────────────────
  const downloadBtn = document.getElementById('downloadBtn');
  const closeDownload = document.getElementById('closeDownloadBtn');
  const confirmDownload = document.getElementById('confirmDownloadBtn');

  downloadBtn && downloadBtn.addEventListener('click', () => show('downloadModal'));
  closeDownload && closeDownload.addEventListener('click', () => hide('downloadModal'));
  confirmDownload && confirmDownload.addEventListener('click', () => {
    downloadCV();
    hide('downloadModal');
  });
  document.getElementById('certificateContainer').addEventListener('click', (e) => {
    // find the closest button with class .remove-certificate
    const btn = e.target.closest('button.remove-certificate');
    if (!btn) return;
  
    const idx = parseInt(btn.dataset.index, 10);
  
    // 1) Remove from the in-memory array
    window.currentCertificates.splice(idx, 1);
  
    // 2) Remove the visible box
    const box = document.querySelector(`#certificateContainer div[data-index="${idx}"]`);
    if (box) box.remove();
  
    // 3) Remove its hidden inputs
    const hiddenContainer = document.getElementById('hiddenCertificates');
    hiddenContainer
      .querySelectorAll(`input[name^="certificates[${idx}]"]`)
      .forEach(i => i.remove());
  
    // 4) Re-index the remaining certificates in the DOM & hidden inputs
    //    (so their data-index attributes and input names match the array)
    window.currentCertificates.forEach((cert, newIdx) => {
      const el = document.querySelector(`#certificateContainer div[data-index="${newIdx + 1}"]`);
      if (el) el.dataset.index = newIdx;
      hiddenContainer
        .querySelectorAll(`input[name^="certificates[${newIdx + 1}]"]`)
        .forEach(i => {
          i.name = i.name.replace(`certificates[${newIdx + 1}]`, `certificates[${newIdx}]`);
        });
    });
  
    // 5) Finally, re-render the preview
    window.updateCertificatesSection();
  });
  // ─── LANGUAGE SECTION (Inline, No Modal) ───────────
  const addLangBtn = document.getElementById('addLanguageBtn');
  const languageDropdown = document.getElementById('languagesDropdown');
  const languageError = document.getElementById('languageErrorLabel');
  const langLevelContainer = document.getElementById('languageLevelContainer');
  const langLevelError = document.getElementById('levelErrorLabel');
  const langDots = document.querySelectorAll('#levelDots span');
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
    const label = (typeof level === 'number' || !isNaN(parseInt(level, 10)))
    ? levelName(parseInt(level, 10))
    : level;
    const data = { name: language, level: label };
    console.log(label);
   

    // Track in data model
    window.currentLanguages.push(data);

    // Create the box
    const box = document.createElement('div');
    box.className = [
      'd-flex',
      'align-items-center',
      'justify-content-between',
      'bg-secondary',       // secondary background
      'bg-opacity-75',      // darker background
      'text-white',
      'rounded',
      'px-3',
      'py-2',
      'mb-2',
      'shadow-lg'           // stronger box-shadow
    ].join(' ');

    box.innerHTML = `
      <div>
        <strong>${language}</strong>
        <span class="ms-2">${label}</span>
      </div>
      <button type="button" class="btn p-1 ms-2" aria-label="Remove">
        <span class="text-danger fs-3">&times;</span>  <!-- larger × -->
      </button>
    `;

    // Hide chosen option in dropdown
    const dropdownOption = document.querySelector(`#languagesDropdown option[value="${language}"]`);
    if (dropdownOption) dropdownOption.style.display = 'none';

    // Add hidden inputs for form submit
    ['name', 'level'].forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `languages[${index}][${key}]`;
            input.value = key === 'name'
              ? language
              : level;      // ← now passes “3”, “4”, “5”, etc.
           hidden.appendChild(input);
    });

    // Remove handler
    box.querySelector('button').addEventListener('click', () => {
      // remove visual box
      preview.removeChild(box);
      // remove hidden inputs
      Array.from(hidden.querySelectorAll(`input[name^="languages[${index}]"]`))
        .forEach(i => hidden.removeChild(i));
      // update data model & preview
      window.currentLanguages = window.currentLanguages.filter((_, i) => i !== index);
      window.updateLanguagesSection();

      // re-add option to dropdown and sort
      const opt = document.createElement('option');
      opt.value = language;
      opt.textContent = language;
      document.getElementById('languagesDropdown').appendChild(opt);
      sortDropdownOptions();
    });

    // Insert and refresh
    preview.appendChild(box);
    window.updateLanguagesSection();
  }


  // Sort dropdown options alphabetically
  function sortDropdownOptions() {
    const dropdown = document.getElementById('languagesDropdown');
    const options = Array.from(dropdown.options);
    options.sort((a, b) => a.textContent.localeCompare(b.textContent));
    options.forEach(option => dropdown.appendChild(option));
  }
  /**
 * Seed an experience card + hidden inputs from a data object,
 * instead of reading from the form.
 */
function addExperienceEntry(data) {
  window.currentExperiences.push(data);
  // Append to the left-hand experience container
  // Store the current index (experienceCount) for this entry.
  const experienceIndex = experienceCount;
  // Create a container for the entry with a data-index attribute.
  const entry = document.createElement('div');
  entry.dataset.index = experienceIndex;
  entry.className = [
    "d-flex",
    "flex-column",
    "bg-secondary", "bg-opacity-75",
    "text-white",
    "rounded",
    "px-3", "py-2", "mb-2",
    "shadow-sm"
  ].join(" ");

  entry.innerHTML = `
  <div class="d-flex justify-content-between align-items-start w-100">
    <div class="flex-grow-1">
      <div class="edu-main">
        <div>
          <h5 class="edu-degree">${data.position}</h5>
          <div class="edu-institution">${data.location}</div>
        </div>
        <div class="edu-meta">
          ${data.description ? `<div class="edu-honor">${data.description}</div>` : ""}
          <div class="edu-year">${data.startDate} — ${data.endDate}</div>
        </div>
      </div>
    </div>
 <button 
type="button" 
class="btn p-1 ms-2 remove-experience" 
data-index="${experienceIndex}" 
aria-label="Remove"
>
<span class="text-danger fs-3">&times;</span>
</button>
  </div>
`;

  document.getElementById('experienceContainer').appendChild(entry);
  experienceCount++;
  window.updateExperienceSection();
  window.updateEducationSection();
}
function addCertificateEntry(data) {
  // 1) push into your in-memory array
  window.currentCertificates.push(data);

  // 2) grab a fresh index
  const idx = certificateCount;

  // 3) build the “card”
  const box = document.createElement('div');
  box.dataset.index = idx;
  box.className = [
    'd-flex', 'flex-column',
    'bg-secondary','bg-opacity-75',
    'text-white','rounded',
    'px-3','py-2','mb-2',
    'shadow-sm'
  ].join(' ');

  box.innerHTML = `
    <div class="d-flex justify-content-between align-items-start w-100">
      <div class="flex-grow-1">
        <div class="cert-main">
          <div>
            <div class="cert-title">${data.name}</div>
            <div class="cert-issuer">Issued by: ${data.association}</div>
          </div>
          <div class="cert-meta">
            <div class="cert-date">${data.date}</div>
          </div>
        </div>
        ${data.description
          ? `<ul class="cert-bullets"><li>${data.description}</li></ul>` 
          : ''}
      </div>
      <button
        type="button"
        class="btn p-1 ms-2 remove-certificate text-danger fs-3"
        data-index="${idx}"
        aria-label="Remove"
      >
        <span class="text-danger fs-3">&times;</span>
      </button>
    </div>
  `;

  // 4) append to the visual container
  document.getElementById('certificateContainer').appendChild(box);

  // 5) mirror into hidden inputs for form submission
  const hidden = document.getElementById('hiddenCertificates');
  ['name','association','description','date','media'].forEach(key => {
    const i = document.createElement('input');
    i.type  = 'hidden';
    i.name  = `certificates[${idx}][${key}]`;
    i.value = data[key];
    hidden.appendChild(i);
  });

  // 6) bump counter & refresh preview panels
  certificateCount++;
  window.updateCertificatesSection();
}

// expose it globally so your Twig‐inline script can call it:
window.addExperienceEntry = addExperienceEntry;

  // Global helper for inline onchange (if used inline in HTML)
  window.addSkillTag = addSkillTag;
  window.updateHiddenSkills = updateHiddenSkills;
  window.addLanguageBox       = addLanguageBox;
window.addExperienceEntry   = addExperienceEntry;      // re-uses your existing addExperience()
window.addCertificateEntry  = addCertificateEntry;     // re-uses addCertificate()

});

