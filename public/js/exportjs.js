// exportjs.js

// ─── Inject CSS Styling ───────────────────────────
// ─── Inject CSS Styling ───────────────────────────
// ─── Inject CSS Styling ───────────────────────────
// exportjs.js

// ─── Inject CSS Styling ───────────────────────────
(function () {
    const css = `
      /* ─── BASE FONT & CONTAINER ───────────────── */
      #cvPreviewContainer {
        font-family: Georgia, 'Times New Roman', serif;
        color: #333333;
        line-height: 1.5;               /* comfortable reading */
           max-width: 42rem;              /* ~672px line length */
        margin: 2rem auto;             /* 2rem top/bottom, centered */
        padding: 1.25rem;              /* 20px all around */
        background-color: #fff;
        border: 0.125rem solid #3B261D;/* 2px brown */
        border-radius: 0.3125rem;      /* 5px corners */
        display: flex;
        flex-direction: column;
        gap: 1.25rem;                  /* 20px between major sections */
      }
    

      /* ─── HEADER (NAME & ROLE) ────────────────── */
         /* ─── HEADER SECTION ─────────────────────────────── */
      #headerContainer {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.25rem;                  /* 4px between name & role */
        margin-bottom: 1.5rem;         /* extra space below header */
      }
      .cv-header-name {
        font-family: 'Merriweather', serif;
        font-size: 1.5rem;              /* 24px */
        font-weight: 700;
        line-height: 1.2;
        color: #3B261D;
        margin-bottom: 0.25rem;         /* 4px under name */
        letter-spacing: 0.02em;
      }
      .cv-header-role {
        font-family: 'Open Sans', sans-serif;
        font-size: 1.125rem;            /* 18px */
        font-weight: 600;
        line-height: 1.3;
        color: #555555;
        letter-spacing: 0.03em;
        margin: 0;
      }

      /* ─── SECTION TITLES ─────────────────────── */
      .cv-section-header {
        font-family: 'Open Sans', sans-serif;
        font-size: 1.125rem;            /* 18px */
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: 0.05em;
        color: #3B261D;
        text-align: center;
        margin: 1rem 0 0.25rem;         /* 16px top, 4px bottom */
      }

      /* ─── PARAGRAPH TEXT ─────────────────────── */
      .cv-paragraph {
        font-size: 0.875rem;            /* 14px */
        line-height: 1.6;
        color: #333333;
        margin: 0 0 1rem;               /* 16px below each paragraph */
      }

      /* ─── CARD TITLES & SUBTITLES ───────────── */
         .cardcv {
        margin-bottom: 0.75rem;        /* 12px below each card */
      }
      .cardcv-title {
        font-family: 'Open Sans', sans-serif;
        font-size: 1rem;                /* 16px */
        font-weight: 700;
        line-height: 1.4;
        color: #3B261D;
        margin: 0;
      }
      .cardcv-subtitle {
        font-family: 'Open Sans', sans-serif;
        font-size: 0.875rem;            /* 14px */
        font-weight: 600;
        line-height: 1.4;
        color: #3B261D;
        margin: 0;
      }
        /* ─── EXPERIENCE ENTRY ─────────────────────────── */
.exp-entry {
  margin-bottom: 1.5rem;                   /* space between entries */
  font-family: 'Open Sans', sans-serif;
  color: #333333;
}

.exp-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

/* Company on the left */
.exp-company {
  font-size: 1rem;                         /* 16px */
  font-weight: 700;
  color: #3B261D;
  margin: 0;
}

/* Role/title under company */
.exp-role {
  font-size: 0.9375rem;                    /* 15px */
  font-weight: 600;
  color: #555555;
  margin: 0.25rem 0 0;                     /* 4px top gap */
}

/* Meta info (location + dates) on the right, stacked */
.exp-meta {
  text-align: right;
  font-size: 0.875rem;                     /* 14px */
  color: #555555;
  line-height: 1.3;
}

.exp-meta .exp-location {
  margin-bottom: 0.25rem;                  /* 4px under location */
}

/* Bulleted description */
.exp-bullets {
  margin: 0.75rem 0 0 1rem;                /* top 12px, indent 16px */
  padding: 0;
  list-style-type: disc;
}

.exp-bullets li {
  margin-bottom: 0.5rem;                   /* 8px between bullets */
  font-size: 0.875rem;                     /* 14px */
  line-height: 1.6;
}
  /* ─── EDUCATION ENTRY ───────────────────────── */
    .edu-entry {
      margin-bottom: 1.5rem;                  /* space between entries */
      font-family: 'Open Sans', sans-serif;
      color: #333333;
    }

    .edu-main {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    /* Degree title on the left */
    .edu-degree {
      font-size: 1rem;                        /* 16px */
      font-weight: 700;
      color: #3B261D;
      margin: 0;
    }

    /* Institution name under the degree */
    .edu-institution {
      font-size: 0.875rem;                    /* 14px */
      font-weight: 600;
      color: #555555;
      margin: 0.25rem 0 0;                    /* 4px top gap */
    }

    /* Meta info (honor/scholarship + year) on the right */
    .edu-meta {
      text-align: right;
      font-size: 0.875rem;                    /* 14px */
      color: #555555;
      line-height: 1.3;
    }

    .edu-meta .edu-honor {
      margin-bottom: 0.25rem;                 /* 4px under honor */
    }
      /* ─── CERTIFICATE ENTRY ───────────────────────── */
.cert-entry {
  margin-bottom: 1.5rem;                   /* space between entries */
  font-family: 'Open Sans', sans-serif;
  color: #333333;
}

.cert-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.cert-title {
  font-size: 1rem;                         /* 16px */
  font-weight: 700;
  color: #3B261D;
  margin: 0;
}

.cert-issuer {
  font-size: 0.9375rem;                    /* 15px */
  font-weight: 600;
  color: #555555;
  margin: 0.25rem 0 0;                     /* 4px top gap */
}

.cert-meta {
  text-align: right;
  font-size: 0.875rem;                     /* 14px */
  color: #555555;
  line-height: 1.3;
}

.cert-bullets {
  margin: 0.75rem 0 0 1rem;                /* top 12px, indent 16px */
  padding: 0;
  list-style-type: disc;
}
.cert-bullets li {
  margin-bottom: 0.5rem;                   /* 8px between bullets */
  font-size: 0.875rem;                     /* 14px */
  line-height: 1.6;
}


    `;
    const styleTag = document.createElement('style');
    styleTag.type = 'text/css';
    styleTag.appendChild(document.createTextNode(css));
    document.head.appendChild(styleTag);
})();




// ─── Helper Functions ─────────────────────────────
function createSectionHeader(text) {
    const container = document.createElement('div');
    const title = document.createElement('div');
    title.className = 'cv-section-header';
    title.textContent = text;
    const divider = document.createElement('div');
    divider.className = 'cv-section-divider';
    container.appendChild(title);
    container.appendChild(divider);
    return container;
}

function createParagraph(text) {
    const p = document.createElement('div');
    p.className = 'cv-paragraph';
    p.textContent = text;
    return p;
}

// ─── 1. Date formatting helper ────────────────────
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;    // fall back if it’s not a valid date
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return d.toLocaleDateString('en-US', options);
}

// ─── 2. Updated createExperienceCard ─────────────
function createExperienceCard(exp) {
    const card = document.createElement('div');
    card.className = 'exp-entry';

    // prepare bullets
    const bullets = (exp.description || '')
        .split('\n')
        .filter(line => line.trim())
        .map(line => line.replace(/^[•\-\*]\s*/, '').trim());

    // format start/end
    const start = formatDate(exp.startDate);
    const end = (exp.endDate && exp.endDate.toLowerCase() !== 'present')
        ? formatDate(exp.endDate)
        : 'Present';

    card.innerHTML = `
      <div class="exp-main">
        <div>
          <div class="exp-company">${exp.company || exp.position}</div>
          <div class="exp-role">${exp.positionDescription || exp.position}</div>
        </div>
        <div class="exp-meta">
          <div class="exp-location">${exp.location}</div>
          <div class="exp-dates">${start} — ${end}</div>
        </div>
      </div>
      <ul class="exp-bullets">
        ${bullets.map(item => `<li>${item}</li>`).join('')}
      </ul>
    `;

    return card;
}



// ─── EDUCATION CARD FACTORY ───────────────────────────
function createEducationCard(ed) {
    const card = document.createElement('div');
    // format start/end
    const start = formatDate(ed.startDate);
    const end = (ed.endDate && ed.endDate.toLowerCase() !== 'present')
        ? formatDate(ed.endDate)
        : 'Present';
    card.className = 'edu-entry';
    card.innerHTML = `
      <div class="edu-main">
        <div>
          <!-- Left: Degree title & institution -->
          <h5 class="edu-degree">${ed.description}</h5>
          <div class="edu-institution">${ed.location}</div>
        </div>
        <div class="edu-meta">
          <!-- Right: optional honor then year -->
          ${ed.position ? `<div class="edu-honor">${ed.position}</div>` : ''}
          <div class="edu-year">${start} — ${end}</div>
        </div>
      </div>
    `;
    return card;
}


function createCertificateCard(cert) {
    const card = document.createElement('div');
    card.className = 'cert-entry';
  
    // break description into bullets
    const bullets = (cert.description || '')
      .split('\n')
      .filter(line => line.trim())
      .map(line => line.replace(/^[•\-\*]\s*/, '').trim());
  
    // format issue date
    const issue = formatDate(cert.date);
  
    card.innerHTML = `
      <div class="cert-main">
        <div>
          <div class="cert-title">${cert.name}</div>
          <div class="cert-issuer">Issued by: ${cert.association}</div>
        </div>
        <div class="cert-meta">
          <div class="cert-date">${issue}</div>
        </div>
      </div>
      ${ bullets.length
         ? `<ul class="cert-bullets">
              ${bullets.map(item => `<li>${item}</li>`).join('')}
            </ul>`
         : ''
      }
    `;
  
    return card;
  }
  
// ─── Section Updaters ─────────────────────────────
function updateHeaderSection() {
    const out = document.getElementById('headerContainer');
    out.innerHTML = '';

    // Your name
    const nameElem = document.createElement('h2');
    nameElem.className = 'cv-header-name';
    nameElem.textContent = window.LOGGED_IN_NAME || 'Aziz Gharbi';
    out.appendChild(nameElem);

    // CV Title
    const titleInput = document.querySelector('input[name="cv[user_title]"]');
    const title = titleInput ? titleInput.value.trim() : '';
    if (title) {
        const role = document.createElement('h3');
        role.className = 'cv-header-role';
        role.textContent = title;
        out.appendChild(role);
    }
}

function updateSummarySection() {
    const out = document.getElementById('summaryContainer');
    out.innerHTML = '';

    const introInput = document.querySelector('textarea[name="cv[introduction]"]');
    const text = introInput ? introInput.value.trim() : '';
    if (!text) return;

    out.appendChild(createSectionHeader('Summary'));
    out.appendChild(createParagraph(text));
}

function updateExperienceSection() {
    const out = document.getElementById('experiencePreviewContainer');
    out.innerHTML = '';
    const items = window.currentExperiences || [];
    const acad = items.filter(e => e.type === 'Internship');
    if (!acad.length) return;
    out.appendChild(createSectionHeader('Experience'));
    items.forEach(exp => out.appendChild(createExperienceCard(exp)));
}

function updateEducationSection() {
    const out = document.getElementById('educationPreviewContainer');
    out.innerHTML = '';
    const items = window.currentExperiences || [];
    const acad = items.filter(e => e.type === 'Academic');
    if (!acad.length) return;
    out.appendChild(createSectionHeader('Education'));
    acad.forEach(ed => out.appendChild(createEducationCard(ed)));
}

function updateSkillsSection() {
    const out = document.getElementById('skillsPreviewContainer');
    out.innerHTML = '';
    const skills = window.selectedSkills || [];
    if (!skills.length) return;
    out.appendChild(createSectionHeader('Skills'));
    out.appendChild(createParagraph(skills.join(', ')));
}

function updateLanguagesSection() {
    const out = document.getElementById('languagesPreviewContainer');
    out.innerHTML = '';
    const langs = window.currentLanguages || [];
    if (!langs.length) return;
    out.appendChild(createSectionHeader('Languages'));
    langs.forEach(l => out.appendChild(createParagraph(`${l.name} – ${l.level}`)));
}

function updateCertificatesSection() {
    const out = document.getElementById('certificatesPreviewContainer');
    out.innerHTML = '';
    const certs = window.currentCertificates || [];
    if (!certs.length) return;
    out.appendChild(createSectionHeader('Certificates'));
    certs.forEach(c => out.appendChild(createCertificateCard(c)));
}

// ─── Refresh All ─────────────────────────────────
function refreshCVPreview() {
    updateHeaderSection();
    updateSummarySection();
    updateExperienceSection();
    updateEducationSection();
    updateSkillsSection();
    updateLanguagesSection();
    updateCertificatesSection();
}

// ─── Event Hooks ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Tie input changes to preview refresh
    const titleInput = document.querySelector('input[name="cv[user_title]"]');
    const introInput = document.querySelector('textarea[name="cv[introduction]"]');

    titleInput.addEventListener('input', updateHeaderSection);
    if (introInput) {
        introInput.addEventListener('input', updateSummarySection);
    }
    console.log(titleInput)



    // You should also call refreshCVPreview after any add/remove of experiences, languages, certificates, skills.
    refreshCVPreview();
});

// Expose for external calls
window.refreshCVPreview = refreshCVPreview;
window.updateHeaderSection = updateHeaderSection;
window.updateSummarySection = updateSummarySection;
window.updateExperienceSection = updateExperienceSection;
window.updateEducationSection = updateEducationSection;
window.updateSkillsSection = updateSkillsSection;
window.updateLanguagesSection = updateLanguagesSection;
window.updateCertificatesSection = updateCertificatesSection;
// --- exportjs.js (append at the bottom) ---
(function () {
    const dropdown = document.getElementById('fileTypeDropdown');
    if (!dropdown) return;

    dropdown.addEventListener('change', () => {
        // Only proceed when the user explicitly picks "PDF" (you can adjust the exact value)
        if (dropdown.value !== 'pdf') return;

        // Grab the preview container’s HTML
        const previewEl = document.getElementById('cvPreviewContainer');
        if (!previewEl) return;

        const previewHtml = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="utf-8"/>
          <title>Your CV</title>
          <style>
         
    @page { size: A4 portrait; margin: 0; }
         html, body {
  margin: 0;
  padding: 0;
 width: 210mm;   /* exact A4 width */
      height: 297mm;  /* exact A4 height */
      box-sizing: border-box;
}
       *, *::before, *::after {
      box-sizing: inherit;
    }
#cvPreviewContainer {
  font-family: Georgia, 'Times New Roman', serif;
  color: #333333;
  line-height: 1.5;
  width: 100%;
  max-width: none;
  margin: 0;               /* remove centering */
  padding: 10mm;           /* use mm so it's consistent with A4 */
  background-color: #fff;
  border-radius: 0.3125rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
    

      /* ─── HEADER (NAME & ROLE) ────────────────── */
         /* ─── HEADER SECTION ─────────────────────────────── */
      #headerContainer {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.25rem;                  /* 4px between name & role */
        margin-bottom: 1.5rem;         /* extra space below header */
      }
      .cv-header-name {
        font-family: 'Merriweather', serif;
        width: 100%; height: 100%;
        font-size: 1.5rem;              /* 24px */
        font-weight: 700;
        line-height: 1.2;
        color: #3B261D;
        margin-bottom: 0.25rem;         /* 4px under name */
        letter-spacing: 0.02em;
      }
      .cv-header-role {
        font-family: 'Open Sans', sans-serif;
        font-size: 1.125rem;            /* 18px */
        font-weight: 600;
        line-height: 1.3;
        color: #555555;
        letter-spacing: 0.03em;
        margin: 0;
      }

      /* ─── SECTION TITLES ─────────────────────── */
      .cv-section-header {
        font-family: 'Open Sans', sans-serif;
        font-size: 1.125rem;            /* 18px */
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: 0.05em;
        color: #3B261D;
        text-align: center;
        margin: 1rem 0 0.25rem;         /* 16px top, 4px bottom */
      }

      /* ─── PARAGRAPH TEXT ─────────────────────── */
      .cv-paragraph {
        font-size: 0.875rem;            /* 14px */
        line-height: 1.6;
        color: #333333;
        margin: 0 0 1rem;               /* 16px below each paragraph */
      }

      /* ─── CARD TITLES & SUBTITLES ───────────── */
         .cardcv {
        margin-bottom: 0.75rem;        /* 12px below each card */
      }
      .cardcv-title {
        font-family: 'Open Sans', sans-serif;
        font-size: 1rem;                /* 16px */
        font-weight: 700;
        line-height: 1.4;
        color: #3B261D;
        margin: 0;
      }
      .cardcv-subtitle {
        font-family: 'Open Sans', sans-serif;
        font-size: 0.875rem;            /* 14px */
        font-weight: 600;
        line-height: 1.4;
        color: #3B261D;
        margin: 0;
      }
        /* ─── EXPERIENCE ENTRY ─────────────────────────── */
.exp-entry {
  margin-bottom: 1.5rem;                   /* space between entries */
  font-family: 'Open Sans', sans-serif;
  color: #333333;
}

.exp-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

/* Company on the left */
.exp-company {
  font-size: 1rem;                         /* 16px */
  font-weight: 700;
  color: #3B261D;
  margin: 0;
}

/* Role/title under company */
.exp-role {
  font-size: 0.9375rem;                    /* 15px */
  font-weight: 600;
  color: #555555;
  margin: 0.25rem 0 0;                     /* 4px top gap */
}

/* Meta info (location + dates) on the right, stacked */
.exp-meta {
  text-align: right;
  font-size: 0.875rem;                     /* 14px */
  color: #555555;
  line-height: 1.3;
}

.exp-meta .exp-location {
  margin-bottom: 0.25rem;                  /* 4px under location */
}

/* Bulleted description */
.exp-bullets {
  margin: 0.75rem 0 0 1rem;                /* top 12px, indent 16px */
  padding: 0;
  list-style-type: disc;
}

.exp-bullets li {
  margin-bottom: 0.5rem;                   /* 8px between bullets */
  font-size: 0.875rem;                     /* 14px */
  line-height: 1.6;
}
  /* ─── EDUCATION ENTRY ───────────────────────── */
    .edu-entry {
      margin-bottom: 1.5rem;                  /* space between entries */
      font-family: 'Open Sans', sans-serif;
      color: #333333;
    }

    .edu-main {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
      

    /* Degree title on the left */
    .edu-degree {
      font-size: 1rem;                        /* 16px */
      font-weight: 700;
      color: #3B261D;
      margin: 0;
    }

    /* Institution name under the degree */
    .edu-institution {
      font-size: 0.875rem;                    /* 14px */
      font-weight: 600;
      color: #555555;
      margin: 0.25rem 0 0;                    /* 4px top gap */
    }

    /* Meta info (honor/scholarship + year) on the right */
    .edu-meta {
      text-align: right;
      font-size: 0.875rem;                    /* 14px */
      color: #555555;
      line-height: 1.3;
    }

    .edu-meta .edu-honor {
      margin-bottom: 0.25rem;                 /* 4px under honor */
    }
      .edu-degree, .edu-institution, .edu-meta {
      overflow-wrap: break-word;
      word-wrap: break-word;
      hyphens: auto;
    }
      /* ─── CERTIFICATE ENTRY ───────────────────────── */
.cert-entry {
  margin-bottom: 1.5rem;                   /* space between entries */
  font-family: 'Open Sans', sans-serif;
  color: #333333;
}

.cert-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.cert-title {
  font-size: 1rem;                         /* 16px */
  font-weight: 700;
  color: #3B261D;
  margin: 0;
}

.cert-issuer {
  font-size: 0.9375rem;                    /* 15px */
  font-weight: 600;
  color: #555555;
  margin: 0.25rem 0 0;                     /* 4px top gap */
}

.cert-meta {
  text-align: right;
  font-size: 0.875rem;                     /* 14px */
  color: #555555;
  line-height: 1.3;
}

.cert-bullets {
  margin: 0.75rem 0 0 1rem;                /* top 12px, indent 16px */
  padding: 0;
  list-style-type: disc;
}
.cert-bullets li {
  margin-bottom: 0.5rem;                   /* 8px between bullets */
  font-size: 0.875rem;                     /* 14px */
  line-height: 1.6;
}

       
          </style>
        </head>
        <body>${previewEl.outerHTML}</body>
        </html>
      `;
        console.log(previewHtml);

        // Send to Symfony route that returns a PDF
        fetch('/cv/export-pdf', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ html: previewHtml })
        })
            .then(resp => {
                if (!resp.ok) throw new Error('PDF export failed');
                return resp.blob();
            })
            .then(blob => {
                // Download the PDF
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'cv.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            })
            .catch(err => {
                console.error(err);
                alert('Could not generate PDF. Please try again.');
            });
    });
})();
