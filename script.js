const apiUrl = "student_api.php";
const form = document.getElementById("stdForm");
const statusMessage = document.getElementById("statusMessage");
const studentIdInput = document.getElementById("studentId");
const searchInput = document.getElementById("stdSearch");
const imageInput = document.getElementById("stdImage");

function showStatus(message, isError) {
  statusMessage.textContent = message;
    statusMessage.className = ""; 
    
    if (message) {
        statusMessage.classList.add(isError ? "status-error" : "status-success");
    }
}

function fillForm(student) {
  studentIdInput.value = student.id || "";
  document.getElementById("stdName").value = student.name || "";
  document.getElementById("stdAge").value = student.age || "";
  document.getElementById("stdEmail").value = student.email || "";
  document.getElementById("stdCourse").value = student.course || "";
  document.getElementById("stdYearLevel").value = String(
    student.year_level || "1",
  );
  document.getElementById("stdGraduating").checked =
    Number(student.graduating) === 1;
  const preview = document.getElementById("imagePreview");
    if (student.image_path) {
        preview.src = student.image_path;
        preview.style.display = "block"; 
    } else {
        preview.style.display = "none";
    }

    imageInput.required = false;
}

function clearFormForCreate() {
  studentIdInput.value = "";
  imageInput.required = true;

  const preview = document.getElementById("imagePreview");
  if (preview) {
        preview.src = "";
        preview.style.display = "none";
    }
}

async function parseResponse(response) {
  let payload;
  try {
    payload = await response.json();
  } catch (error) {
    throw new Error("Server returned an invalid response.");
  }

  if (!response.ok || !payload.success) {
    throw new Error(payload.message || "Request failed.");
  }

  return payload;
}

function disableSubmit() {
  const btnSubmit = document.getElementById("btnSubmit");
  btnSubmit.setAttribute("disabled", "");
}

function enableSubmit() {
  const btnSubmit = document.getElementById("btnSubmit");
  btnSubmit.removeAttribute("disabled");
}

document.getElementById("btnReset").addEventListener("click", function () {
  enableSubmit();
});


form.addEventListener("submit", async function (event) {
  event.preventDefault();

  const formData = new FormData(form);
  if (!document.getElementById("stdGraduating").checked) {
    formData.set("graduating", "0");
  }

  try {
    const response = await fetch(apiUrl + "?action=create", {
      method: "POST",
      body: formData,
    });
    const payload = await parseResponse(response);
    const student = payload.data.student;
    form.reset();
    clearFormForCreate(); 
    searchInput.value = "";
    showStatus(payload.message + " ID: " + student.id, false);
  } catch (error) {
    showStatus(error.message, true);
  }
});

document
  .getElementById("btnSearch")
  .addEventListener("click", async function () {
    const id = Number(searchInput.value);
    if (!id) {
      showStatus("Enter a valid student ID first.", true);
      return;
    }

    disableSubmit();

    try {
      const response = await fetch(
        apiUrl + "?action=search&id=" + encodeURIComponent(id),
      );
      const payload = await parseResponse(response);
      fillForm(payload.data.student);
      showStatus(payload.message, false);
    } catch (error) {
      form.reset();
      clearFormForCreate();
      showStatus(error.message, true);
    }
  });

document
  .getElementById("btnUpdate")
  .addEventListener("click", async function () {
    const id = Number(studentIdInput.value || searchInput.value);
    if (!id) {
      showStatus("Search for a student before updating.", true);
      return;
    }

    if (!form.reportValidity()) {
      return;
    }

    const formData = new FormData(form);
    formData.set("id", String(id));
    if (!document.getElementById("stdGraduating").checked) {
      formData.set("graduating", "0");
    }

    try {
      const response = await fetch(apiUrl + "?action=update", {
        method: "POST",
        body: formData,
      });
      const payload = await parseResponse(response);
      fillForm(payload.data.student);
      searchInput.value = payload.data.student.id;
      showStatus(payload.message, false);
    } catch (error) {
      showStatus(error.message, true);
    }
  });

document
  .getElementById("btnDelete")
  .addEventListener("click", async function () {
    const id = Number(studentIdInput.value || searchInput.value);
    if (!id) {
      showStatus("Search for a student before deleting.", true);
      return;
    }

    const body = new FormData();
    body.set("id", String(id));

    try {
      const response = await fetch(apiUrl + "?action=delete", {
        method: "POST",
        body,
      });
      const payload = await parseResponse(response);
      form.reset();
      searchInput.value = "";
      clearFormForCreate();
      showStatus(payload.message, false);
    } catch (error) {
      showStatus(error.message, true);
    }
  });

form.addEventListener("reset", function () {
  clearFormForCreate();
  showStatus("", false);
});
