const courseCells = document.querySelectorAll(
    ".schedule-row:not(.schedule-head) span:not(:first-child)"
);

const resetButton = document.querySelector("#reset-schedule");

// 顯示課程資料
function showCourse(cell, course, room, teacher) {
    cell.innerHTML = "";

    cell.appendChild(document.createTextNode(course));

    if (room !== "") {
        const roomText = document.createElement("small");
        roomText.className = "room";
        roomText.textContent = room;
        cell.appendChild(roomText);
    }

    if (teacher !== "") {
        const teacherText = document.createElement("small");
        teacherText.className = "teacher";
        teacherText.textContent = teacher;
        cell.appendChild(teacherText);
    }
}

// 讀取之前儲存的課表
const savedSchedule = JSON.parse(
    localStorage.getItem("schedule")
);

if (savedSchedule) {
    courseCells.forEach(function (cell, index) {
        const data = savedSchedule[index];

        if (data) {
            showCourse(
                cell,
                data.course || "",
                data.room || "",
                data.teacher || ""
            );
        }
    });
}

// 點格子修改
courseCells.forEach(function (cell) {
    cell.addEventListener("click", function () {

        const oldCourse =
            Array.from(cell.childNodes)
                .find(function (node) {
                    return node.nodeType === Node.TEXT_NODE;
                })
                ?.textContent.trim() || "";

        const oldRoom =
            cell.querySelector(".room")?.textContent.trim() ||
            cell.querySelector("small")?.textContent.trim() ||
            "";

        const oldTeacher =
            cell.querySelector(".teacher")?.textContent.trim() || "";

        const newCourse = prompt(
            "請輸入課程名稱",
            oldCourse
        );

        if (newCourse === null) {
            return;
        }

        const newRoom = prompt(
            "請輸入教室",
            oldRoom
        );

        if (newRoom === null) {
            return;
        }

        const newTeacher = prompt(
            "請輸入老師姓名",
            oldTeacher
        );

        if (newTeacher === null) {
            return;
        }

        showCourse(
            cell,
            newCourse.trim(),
            newRoom.trim(),
            newTeacher.trim()
        );

        const scheduleData = [];

        courseCells.forEach(function (courseCell) {

            const course =
                Array.from(courseCell.childNodes)
                    .find(function (node) {
                        return node.nodeType === Node.TEXT_NODE;
                    })
                    ?.textContent.trim() || "";

            const room =
                courseCell.querySelector(".room")
                    ?.textContent.trim() || "";

            const teacher =
                courseCell.querySelector(".teacher")
                    ?.textContent.trim() || "";

            scheduleData.push({
                course: course,
                room: room,
                teacher: teacher
            });
        });

        localStorage.setItem(
            "schedule",
            JSON.stringify(scheduleData)
        );
    });
});

// 重設課表
resetButton.addEventListener("click", function () {
    const confirmed = confirm("確定要重設課表嗎？");

    if (!confirmed) {
        return;
    }

    localStorage.removeItem("schedule");
    location.reload();
});
const today = new Date().getDay();

const dayHeaders = document.querySelectorAll(
    ".schedule-head span:not(:first-child)"
);

if (today >= 1 && today <= 5) {
    dayHeaders[today - 1].classList.add("today");
}