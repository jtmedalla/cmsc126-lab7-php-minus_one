<!DOCTYPE html>
<html>
    <head>
        <title>Student Registration Form</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>Student Registration Form</h1>
        <h2>All fields marked * are required</h2>
        <p id="statusMessage" aria-live="polite"></p>
        <!-- Student Infomation form -->
        <form id="stdForm" enctype="multipart/form-data">
            <input type="hidden" id="studentId" name="id">
            <h3>Personal Information</h3>
            <label for="stdNname">Name</label><br>
            <input type="text" id="stdName" name="name" maxlength="40" required><br><br>
            <label for="stdAge">Age</label><br>
            <input type="number" id="stdAge" name="age" min="0" max="99" placeholder="0-99" required><br><br>
            <label for="stdEmail">Email</label><br>
            <input type="email" id="stdEmail" name="email" maxlength="40" required><br><br>
            <small>Must be a valid email address (max 40 characters)</small>
            <h3>Academic Information</h3>
            <label for="stdCourse">Course</label><br>
            <input type="text" id="stdCourse" name="course" maxlength="40" required><br><br>
            <label for="stdYearLevel">Year Level</label><br>
            <select id="stdYearLevel" name="year_level" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select><br><br>
            <p>Graduating this year?</p>
            <input type="checkbox" id="stdGraduating" name="graduating" value="1">
            <label for="stdGraduating">Yes</label>
            <h3>Profile Photo</h3>
            <label for="stdImage">Profile Image</label><br>
            <input type="file" id="stdImage" name="image" accept="image/*" required><br><br>
            <input type="submit">
            <input type="reset">
        </form>
        <br><br><br><br>
        <!-- Student Record Section-->
        <h3>Student Record Search</h3>
        <label for="stdSearch">Look up by Student ID</label><br>
        <input type="number" id="stdSearch">
        <button type="button" id="btnSearch">Search</button>
        <button type="button" id="btnUpdate">Update</button>
        <button type="button" id="btnDelete">Delete</button>

        <script>
            const apiUrl = "student_api.php";
            const form = document.getElementById("stdForm");
            const statusMessage = document.getElementById("statusMessage");
            const studentIdInput = document.getElementById("studentId");
            const searchInput = document.getElementById("stdSearch");
            const imageInput = document.getElementById("stdImage");

            function showStatus(message, isError) {
                statusMessage.textContent = message;
                statusMessage.style.color = isError ? "#b00020" : "#006400";
            }

            function fillForm(student) {
                studentIdInput.value = student.id || "";
                document.getElementById("stdName").value = student.name || "";
                document.getElementById("stdAge").value = student.age || "";
                document.getElementById("stdEmail").value = student.email || "";
                document.getElementById("stdCourse").value = student.course || "";
                document.getElementById("stdYearLevel").value = String(student.year_level || "1");
                document.getElementById("stdGraduating").checked = Number(student.graduating) === 1;
                imageInput.required = false;
            }

            function clearFormForCreate() {
                studentIdInput.value = "";
                imageInput.required = true;
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

            form.addEventListener("submit", async function(event) {
                event.preventDefault();

                const formData = new FormData(form);
                if (!document.getElementById("stdGraduating").checked) {
                    formData.set("graduating", "0");
                }

                try {
                    const response = await fetch(apiUrl + "?action=create", {
                        method: "POST",
                        body: formData
                    });
                    const payload = await parseResponse(response);
                    const student = payload.data.student;
                    fillForm(student);
                    searchInput.value = student.id;
                    showStatus(payload.message + " ID: " + student.id, false);
                } catch (error) {
                    showStatus(error.message, true);
                }
            });

            document.getElementById("btnSearch").addEventListener("click", async function() {
                const id = Number(searchInput.value);
                if (!id) {
                    showStatus("Enter a valid student ID first.", true);
                    return;
                }

                try {
                    const response = await fetch(apiUrl + "?action=search&id=" + encodeURIComponent(id));
                    const payload = await parseResponse(response);
                    fillForm(payload.data.student);
                    showStatus(payload.message, false);
                } catch (error) {
                    showStatus(error.message, true);
                }
            });

            document.getElementById("btnUpdate").addEventListener("click", async function() {
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
                        body: formData
                    });
                    const payload = await parseResponse(response);
                    fillForm(payload.data.student);
                    searchInput.value = payload.data.student.id;
                    showStatus(payload.message, false);
                } catch (error) {
                    showStatus(error.message, true);
                }
            });

            document.getElementById("btnDelete").addEventListener("click", async function() {
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
                        body
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

            form.addEventListener("reset", function() {
                clearFormForCreate();
                showStatus("", false);
            });
        </script>
    </body>
</html>
