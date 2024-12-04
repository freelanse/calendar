document.addEventListener("DOMContentLoaded", () => {
    const calendarContainer = document.getElementById("calendar-container");
    const calendarMonth = document.getElementById("calendar-month");
    const tooltip = document.createElement("div");
    tooltip.className = "calendar-tooltip";
    document.body.appendChild(tooltip);

    if (!calendarContainer || !calendarMonth) {
        console.error("Не найдены элементы календаря: #calendar-container или #calendar-month");
        return;
    }

    let currentDate = new Date();

    // Рендер календаря
    function renderCalendar(date) {
        calendarContainer.innerHTML = "";

        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        // Установка текста месяца
        calendarMonth.textContent = date.toLocaleDateString("ru-RU", {
            month: "long",
            year: "numeric",
        });

        // Получение данных о записях
        fetch(calendarAjax.ajaxUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_posts_for_month&year=${year}&month=${month + 1}`,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success && data.data) {
                    const postsByDate = data.data; // Объект с датами и записями

                    // Дни месяца
                    for (let day = 1; day <= lastDay.getDate(); day++) {
                        const dayCell = document.createElement("div");
                        dayCell.className = "calendar-day";
                        dayCell.textContent = day;

                        const fullDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
                        dayCell.dataset.date = fullDate;

                        if (postsByDate[fullDate]) {
                            // Дата с записями
                            dayCell.classList.add("has-posts");
                            dayCell.addEventListener("mouseover", () => showTooltip(dayCell, fullDate));
                            dayCell.addEventListener("mouseleave", hideTooltip);
                            dayCell.addEventListener("click", () => {
                                window.location.href = `/date/${fullDate}`;
                            });
                        } else {
                            // Дата без записей
                            dayCell.classList.add("no-posts");
                        }

                        calendarContainer.appendChild(dayCell);
                    }
                } else {
                    console.error("Ошибка получения данных для календаря");
                }
            });
    }

    // Показать подсказку
    function showTooltip(dayCell, date) {
        fetch(calendarAjax.ajaxUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_posts_for_date&date=${date}`,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success && data.data.length > 0) {
                    tooltip.innerHTML = data.data
                        .map((post) => `<a href="${post.link}">${post.title}</a>`)
                        .join("<br>");
                    tooltip.style.display = "block";
                    tooltip.style.top = dayCell.getBoundingClientRect().bottom + window.scrollY + "px";
                    tooltip.style.left = dayCell.getBoundingClientRect().left + "px";
                } else {
                    tooltip.style.display = "none";
                }
            });
    }

    // Скрыть подсказку
    function hideTooltip() {
        
        tooltip.style.display = "none";
    }

    // Перелистывание
    document.getElementById("prev-month").addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    document.getElementById("next-month").addEventListener("click", () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    renderCalendar(currentDate);
    
    
    
});
