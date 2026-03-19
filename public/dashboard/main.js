// Attach the event listener to the dropdown
//  recordTypeDropdown.addEventListener("change", toggleFields);

// Trigger the toggle function on page load (to handle pre-selected values)
//  toggleFields();


//  function fetchActivities() {
//      const subjectId = document.getElementById('subject_id').value;

//      if (subjectId) {
//          // Fetch activities for the selected subject
//          const formData = new FormData();
//          formData.append('subject_id', subjectId);

//          fetch('fetchActivities.php', {
//                  method: 'POST',
//                  body: formData
//              })
//              .then(response => response.json())
//              .then(data => {
//                  let activitySelect = document.getElementById('activity_id');
//                  activitySelect.innerHTML = '<option value="">-- Choose Activity --</option>'; // Reset activities

//                  if (data.activities.length > 0) {
//                      data.activities.forEach(activity => {
//                          let option = document.createElement('option');
//                          option.value = activity.id;
//                          option.textContent = activity.activity_name;
//                          activitySelect.appendChild(option);
//                      });
//                      document.getElementById('activitySection').style.display = 'block';
//                  } else {
//                      document.getElementById('activitySection').style.display = 'none';
//                  }
//              })
//              .catch(error => console.error('Error fetching activities:', error));
//      } else {
//          document.getElementById('activitySection').style.display = 'none';
//      }
//  }

//  document.getElementById('activityForm').addEventListener('submit', function(event) {
//      event.preventDefault();

//      const subjectId = document.getElementById('subject_id').value;
//      const activityId = document.getElementById('activity_id').value;

//      if (subjectId && activityId) {
//          const formData = new FormData();
//          formData.append('subject_id', subjectId);
//          formData.append('activity_id', activityId);

//          fetch('fetchStudents.php', {
//                  method: 'POST',
//                  body: formData
//              })
//              .then(response => response.json())
//              .then(data => {
//                  let studentsList = document.getElementById('studentsList');
//                  studentsList.innerHTML = ''; // Reset student list

//                  if (data.students.length > 0) {
//                      data.students.forEach(student => {
//                          let row = document.createElement('tr');
//                          row.innerHTML = `
//                         <td class="px-4 py-2 border">${student.student_id}</td>
//                         <td class="px-4 py-2 border">${student.usn}</td>
//                         <td class="px-4 py-2 border">${student.full_name}</td>
//                         <td class="px-4 py-2 border">${student.email}</td>
//                         <td class="px-4 py-2 border">
//                             <form method="POST" action="saveActivityScores.php">
//                                 <input type="hidden" name="student_id" value="${student.student_id}">
//                                 <input type="number" name="activity_score" placeholder="Enter score" required>
//                                 <input type="hidden" name="activity_id" value="${activityId}">
//                                 <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Score</button>
//                             </form>
//                         </td>
//                     `;
//                          studentsList.appendChild(row);
//                      });
//                      document.getElementById('studentsSection').style.display = 'block';
//                  } else {
//                      document.getElementById('studentsSection').style.display = 'none';
//                  }
//              })
//              .catch(error => console.error('Error fetching students:', error));
//      }
//  });