// Admin settings - Dynamic time intervals
const adminSettings = {
    availableDays: [1, 2, 3, 4, 5], // Monday to Friday (0=Sunday, 6=Saturday)
    startTime: "08:00",
    endTime: "17:00",
    timeInterval: 15, // Dynamic interval in minutes
    bookedSlots: window.bookedSlots
};

// Tennis lesson pricing
const rateStructure = {
    "Private": {
        hourly: 90,
        "45min": 70,
        "30min": 50
    },
    "Semi-Private": {
        hourly: 50,
        "45min": 40,
        "30min": 30
    },
    "Group": {
        hourly: 35,
        "45min": 30,
        "30min": 25
    },
    "Cardio Tennis": {
        hourly: 25,
        "45min": 20,
        "30min": 15
    }
};

// Player limits per lesson type
const playerLimits = {
    "Private": {min: 1, max: 1},
    "Semi-Private": {min: 2, max: 2},
    "Group": {min: 3, max: 8},
    "Cardio Tennis": {min: 4, max: 10}
};

let currentDate = new Date();
let selectedDate = null;
let selectedTime = null;
let lessons = [];
let currentStep = 1;
let lessonIdCounter = 1;

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

function initCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update header
    document.getElementById('currentMonth').textContent = `${months[month]} ${year}`;

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const calendarDates = document.getElementById('calendarDates');
    calendarDates.innerHTML = '';

    // Generate 6 weeks of dates
    for (let week = 0; week < 6; week++) {
        for (let day = 0; day < 7; day++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + (week * 7) + day);

            const dateElement = createDateElement(date, month);
            calendarDates.appendChild(dateElement);
        }
    }
}

function createDateElement(date, currentMonth) {
    const dateElement = document.createElement('div');
    dateElement.className = 'calendar-date';

    const isCurrentMonth = date.getMonth() === currentMonth;
    const isToday = isDateToday(date);
    const isAvailable = isDateAvailable(date);
    const isPast = date < new Date().setHours(0, 0, 0, 0);

    if (!isCurrentMonth) {
        dateElement.classList.add('other-month');
    }

    if (isPast || !isAvailable) {
        dateElement.classList.add('disabled');
    }

    if (isToday) {
        dateElement.classList.add('today');
    }

    const dateNumber = document.createElement('div');
    dateNumber.className = 'date-number';
    dateNumber.textContent = date.getDate();
    dateElement.appendChild(dateNumber);

    // Add availability indicator for current month dates
    if (isCurrentMonth && !isPast) {
        const indicator = document.createElement('div');
        indicator.className = 'availability-indicator';

        if (isAvailable) {
            const dateStr = formatDate(date);
            const bookedCount = adminSettings.bookedSlots[dateStr]?.length || 0;
            const totalSlots = getTotalSlots();

            if (bookedCount === 0) {
                indicator.classList.add('available');
            } else if (bookedCount < totalSlots * 0.8) {
                indicator.classList.add('limited');
            } else {
                indicator.classList.add('unavailable');
            }
        } else {
            indicator.classList.add('unavailable');
        }

        dateElement.appendChild(indicator);
    }

    // Add click handler for available dates
    if (isCurrentMonth && !isPast && isAvailable) {
        dateElement.addEventListener('click', () => selectDate(date, dateElement));
    }

    return dateElement;
}

function isDateToday(date) {
    const today = new Date();
    return date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear();
}

function isDateAvailable(date) {
    const dayOfWeek = date.getDay();
    return adminSettings.availableDays.includes(dayOfWeek);
}

function formatDate(date) {
    return date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0');
}

function getTotalSlots() {
    const start = timeToMinutes(adminSettings.startTime);
    const end = timeToMinutes(adminSettings.endTime);
    return Math.floor((end - start) / adminSettings.timeInterval);
}

function timeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

function minutesToTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
}

function selectDate(date, dateElement) {
    // Remove previous selection
    document.querySelectorAll('.calendar-date.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selection to clicked date
    dateElement.classList.add('selected');

    selectedDate = date;
    selectedTime = null;

    // Show time panel and populate time slots
    showTimeSlots(date);

    // Hide stepper form and show no selection message
    document.getElementById('stepperForm').style.display = 'none';
    document.getElementById('noSelection').style.display = 'none';
}

function showTimeSlots(date) {
    const timePanel = document.getElementById('timePanel');
    const selectedDateChip = document.getElementById('selectedDateChip');
    const timeSlots = document.getElementById('timeSlots');

    // Format date for display
    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric'
    });
    selectedDateChip.textContent = dateStr;

    // Generate time slots
    const dateKey = formatDate(date);
    const bookedSlots = adminSettings.bookedSlots[dateKey] || [];

    timeSlots.innerHTML = '';

    const startMinutes = timeToMinutes(adminSettings.startTime);
    const endMinutes = timeToMinutes(adminSettings.endTime);

    for (let minutes = startMinutes; minutes < endMinutes; minutes += adminSettings.timeInterval) {
        const timeStr = minutesToTime(minutes);
        const slot = createTimeSlot(timeStr, bookedSlots.includes(timeStr));
        timeSlots.appendChild(slot);
    }

    timePanel.style.display = 'block';
}

function createTimeSlot(time, isBooked) {
    const slot = document.createElement('div');
    slot.className = 'time-slot';
    slot.textContent = formatTimeDisplay(time);

    if (isBooked) {
        slot.classList.add('booked');
    } else {
        slot.classList.add('available');
        slot.addEventListener('click', () => selectTime(time, slot));
    }

    return slot;
}

function formatTimeDisplay(time) {
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
    return `${displayHours}:${String(minutes).padStart(2, '0')} ${period}`;
}

function selectTime(time, element) {
    // Remove previous selection
    document.querySelectorAll('.time-slot.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selection
    element.classList.add('selected');
    selectedTime = time;

    // Show stepper form
    document.getElementById('stepperForm').style.display = 'block';
    document.getElementById('noSelection').style.display = 'none';

    // Initialize with first lesson if no lessons exist
    if (lessons.length === 0) {
        addNewLesson();
    }

    // Update calculations
    updateAllCalculations();
}

// Stepper Functions
function nextStep() {
    if (currentStep === 1) {
        // Validate step 1
        if (!validateStep1()) {
            return;
        }

        // Move to step 2
        currentStep = 2;
        updateStepperUI();
        generateSummary();
    } else if (currentStep === 2) {
        // Final submission
        submitBooking();
        // if (validateFinalSubmission()) {
        //     alert('Booking confirmed! Thank you for your booking.');
        // }
    }
}

function previousStep() {
    if (currentStep === 2) {
        currentStep = 1;
        updateStepperUI();
    }
}

function updateStepperUI() {
    // Update step circles
    document.querySelectorAll('.step-circle').forEach((circle, index) => {
        const stepNum = index + 1;
        circle.classList.remove('active', 'completed');

        if (stepNum < currentStep) {
            circle.classList.add('completed');
        } else if (stepNum === currentStep) {
            circle.classList.add('active');
        }
    });

    // Update step titles
    document.querySelectorAll('.step-title').forEach((title, index) => {
        const stepNum = index + 1;
        title.classList.remove('active');

        if (stepNum === currentStep) {
            title.classList.add('active');
        }
    });

    // Update progress bar
    const progress = ((currentStep - 1) / 1) * 100; // 2 steps total
    document.getElementById('stepperProgress').style.width = progress + '%';

    // Show/hide step content
    document.querySelectorAll('.step-content').forEach((content, index) => {
        const stepNum = index + 1;
        content.classList.remove('active');

        if (stepNum === currentStep) {
            content.classList.add('active');
        }
    });

    // Update buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (currentStep === 1) {
        prevBtn.style.display = 'none';
        nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
    } else if (currentStep === 2) {
        prevBtn.style.display = 'block';
        nextBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Booking';
    }
}

function validateStep1() {
    let isValid = true;
    const errorMessages = [];

    // Validate personal details
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const suburb = document.getElementById('suburb').value;
    const address = document.getElementById('address').value.trim();
    const city = document.getElementById('city').value.trim();
    const postalCode = document.getElementById('postalCode').value.trim();

    if (!fullName) {
        errorMessages.push('Full name is required');
        isValid = false;
    }

    if (!email) {
        errorMessages.push('Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        errorMessages.push('Please enter a valid email address');
        isValid = false;
    }

    if (!suburb) {
        errorMessages.push('Please select your suburb');
        isValid = false;
    }

    if (!address) {
        errorMessages.push('Address is required');
        isValid = false;
    }

    if (!city) {
        errorMessages.push('City is required');
        isValid = false;
    }

    if (!postalCode) {
        errorMessages.push('Postal code is required');
        isValid = false;
    } else if (!/^\d{5}$/.test(postalCode)) {
        errorMessages.push('Postal code must be 5 digits');
        isValid = false;
    }

    // Validate lessons
    lessons.forEach(lesson => {
        if (!lesson.type || !lesson.duration || !lesson.ballLevel) {
            errorMessages.push('Please complete all lesson details');
            isValid = false;
        }
    });

    if (!isValid) {
        // Instead of:
// alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
        showToast(`Please fix the following errors:<br>• ${errorMessages.join('<br>• ')}`, 'error', 8000);
    }

    return isValid;
}

function validateFinalSubmission() {
    // For demo purposes, we'll just show a success message
    return true;
}

function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// Lesson Management Functions
function addNewLesson() {
    const lesson = {
        id: lessonIdCounter++,
        type: '',
        duration: adminSettings.timeInterval, // Use dynamic interval
        players: 1,
        ballLevel: ''
    };

    lessons.push(lesson);
    renderLesson(lesson);
    updateAllCalculations();
}

function renderLesson(lesson) {
    const container = document.getElementById('lessonsContainer');

    const lessonCard = document.createElement('div');
    lessonCard.className = 'lesson-card';
    lessonCard.dataset.lessonId = lesson.id;

    lessonCard.innerHTML = `
                <div class="lesson-header">
                    <div class="lesson-title">Lesson ${lessons.length}</div>
                    <button type="button" class="remove-lesson-btn" onclick="removeLesson(${lesson.id})">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>

                <div class="lesson-grid">
                    <div class="form-group">
                        <label class="form-label">Lesson Type</label>
                        <select class="form-control lesson-type" onchange="updateLessonType(${lesson.id}, this.value)">
                            <option value="">Select Type</option>
                            <option value="Private">Private</option>
                            <option value="Semi-Private">Semi-Private</option>
                            <option value="Group">Group</option>
                            <option value="Cardio Tennis">Cardio Tennis</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <select class="form-control lesson-duration" onchange="updateLessonDuration(${lesson.id}, this.value)">
                            ${generateDurationOptions(lesson.duration)}
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Number of Players</label>
                        <select class="form-control lesson-players" onchange="updateLessonPlayers(${lesson.id}, this.value)">
                            ${generatePlayerOptions(lesson.type, lesson.players)}
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Age - Ball Level</label>
                        <select class="form-control lesson-ball" onchange="updateLessonBallLevel(${lesson.id}, this.value)">
                            <option value="">Select Age - Ball Level</option>
                            <option value="Red">2-8 Years Old - Red Ball</option>
                            <option value="Orange">9-10 Years Old - Orange Ball</option>
                            <option value="Green">11-12 Years Old - Green Ball</option>
                            <option value="Yellow">13+ Years Old - Yellow Ball</option>
                        </select>
                    </div>
                </div>
            `;

    container.appendChild(lessonCard);
    updateRemoveButtons();
}

function generateDurationOptions(selectedDuration) {
    let options = '';
    for (let i = adminSettings.timeInterval; i <= 120; i += adminSettings.timeInterval) {
        const selected = i === selectedDuration ? 'selected' : '';
        options += `<option value="${i}" ${selected}>${i} minutes</option>`;
    }
    return options;
}

function generatePlayerOptions(lessonType, selectedPlayers) {
    let options = '';
    let min = 1, max = 8;

    if (lessonType && playerLimits[lessonType]) {
        min = playerLimits[lessonType].min;
        max = playerLimits[lessonType].max;
    }

    for (let i = min; i <= max; i++) {
        const selected = i === selectedPlayers ? 'selected' : '';
        options += `<option value="${i}" ${selected}>${i} player${i !== 1 ? 's' : ''}</option>`;
    }

    return options;
}

function updateLessonType(lessonId, type) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (lesson) {
        lesson.type = type;

        // Update player options based on lesson type
        const lessonCard = document.querySelector(`[data-lesson-id="${lessonId}"]`);
        const playersSelect = lessonCard.querySelector('.lesson-players');

        if (type && playerLimits[type]) {
            const limits = playerLimits[type];
            playersSelect.innerHTML = generatePlayerOptions(type, lesson.players);
            lesson.players = Math.max(limits.min, Math.min(lesson.players, limits.max));
            playersSelect.value = lesson.players;
            playersSelect.disabled = (type === "Private" || type === "Semi-Private");
        } else {
            playersSelect.innerHTML = generatePlayerOptions('', lesson.players);
            playersSelect.disabled = false;
        }

        updateAllCalculations();
    }
}

function updateLessonDuration(lessonId, duration) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (lesson) {
        lesson.duration = parseInt(duration);
        updateAllCalculations();
    }
}

function updateLessonPlayers(lessonId, players) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (lesson) {
        lesson.players = parseInt(players);
        updateAllCalculations();
    }
}

function updateLessonBallLevel(lessonId, ballLevel) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (lesson) {
        lesson.ballLevel = ballLevel;
        updateAllCalculations();
    }
}

function removeLesson(lessonId) {
    if (lessons.length > 1) {
        lessons = lessons.filter(l => l.id !== lessonId);

        const lessonCard = document.querySelector(`[data-lesson-id="${lessonId}"]`);
        lessonCard.remove();

        updateLessonTitles();
        updateRemoveButtons();
        updateAllCalculations();
    }
}

function updateLessonTitles() {
    document.querySelectorAll('.lesson-card').forEach((card, index) => {
        const title = card.querySelector('.lesson-title');
        title.textContent = `Lesson ${index + 1}`;
    });
}

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-lesson-btn');
    removeButtons.forEach(btn => {
        btn.style.display = lessons.length > 1 ? 'block' : 'none';
    });
}

// Calculation Functions
function updateAllCalculations() {
    updateFinalTime();
    calculateTotalPrice();
    updateHiddenFields();
}

function updateFinalTime() {
    if (!selectedTime) return;

    const totalMinutes = calculateTotalMinutes();
    const selectedMinutes = timeToMinutes(selectedTime);
    const finalMinutes = selectedMinutes + totalMinutes;
    const finalTime = minutesToTime(finalMinutes);

    // This calculation is used in the summary
    return {startTime: selectedTime, endTime: finalTime, totalMinutes};
}

function calculateTotalMinutes() {
    return lessons.reduce((total, lesson) => total + (lesson.duration || 0), 0);
}

function calculateTotalPrice() {
    let total = 0;

    lessons.forEach(lesson => {
        if (lesson.type && rateStructure[lesson.type]) {
            const rates = rateStructure[lesson.type];
            let price = 0;

            if (lesson.type === "Private") {
                if (lesson.duration === 30) {
                    price = rates["30min"];
                } else if (lesson.duration === 45) {
                    price = rates["45min"];
                } else {
                    price = rates.hourly * (lesson.duration / 60);
                }
            } else {
                let pricePerPlayer;
                if (lesson.duration === 30) {
                    pricePerPlayer = rates["30min"];
                } else if (lesson.duration === 45) {
                    pricePerPlayer = rates["45min"];
                } else {
                    pricePerPlayer = rates.hourly * (lesson.duration / 60);
                }
                price = pricePerPlayer * lesson.players;
            }

            total += price;
        }
    });

    return total;
}

function generateSummary() {
    const summaryContainer = document.getElementById('summaryContent');
    const totalPrice = calculateTotalPrice();
    const timeInfo = updateFinalTime();

    // Get personal details
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const description = document.getElementById('description').value.trim();
    const suburb = document.getElementById('suburb').value;
    const address = document.getElementById('address').value.trim();
    const city = document.getElementById('city').value.trim();
    const postalCode = document.getElementById('postalCode').value.trim();
    const state = document.getElementById('state').value;
    const country = document.getElementById('country').value;

    let summaryHTML = '';

    // Personal details section
    summaryHTML += `
            <div class="summary-item">
                <span class="summary-label">Customer:</span>
                <span class="summary-value">${fullName}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Email:</span>
                <span class="summary-value">${email}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Address:</span>
                <span class="summary-value">${address}, ${suburb}, ${city} ${postalCode}, ${state}, ${country}</span>
            </div>
            ${description ? `<div class="summary-item">
                <span class="summary-label">Notes:</span>
                <span class="summary-value">${description}</span>
            </div>` : ''}
            <hr style="margin: 16px 0;">
        `;

    if (selectedDate && selectedTime && timeInfo) {
        const dateStr = selectedDate.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        summaryHTML += `
                    <div class="summary-item">
                        <span class="summary-label">Date:</span>
                        <span class="summary-value">${dateStr}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Start Time:</span>
                        <span class="summary-value">${formatTimeDisplay(timeInfo.startTime)}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">End Time:</span>
                        <span class="summary-value">${formatTimeDisplay(timeInfo.endTime)}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Duration:</span>
                        <span class="summary-value">${timeInfo.totalMinutes} minutes</span>
                    </div>
                    <hr style="margin: 16px 0;">
                `;
    }

    summaryHTML += `
                <div class="summary-item">
                    <span class="summary-label">Total Lessons:</span>
                    <span class="summary-value">${lessons.length}</span>
                </div>
            `;

    lessons.forEach((lesson, index) => {
        summaryHTML += `
                    <div style="margin: 12px 0; padding: 12px; background: rgba(6, 64, 43, 0.05); border-radius: 8px;">
                        <strong>Lesson ${index + 1}:</strong><br>
                        <small>
                            ${lesson.type || 'Not selected'} - ${lesson.duration || 0} min -
                            ${lesson.players || 0} player${lesson.players !== 1 ? 's' : ''} -
                            ${lesson.ballLevel || 'Not selected'}
                        </small>
                    </div>
                `;
    });

    summaryContainer.innerHTML = summaryHTML;
    document.getElementById('summaryTotalPrice').textContent = `Total: $${totalPrice.toFixed(2)}`;
}

function updateHiddenFields() {
    const totalMinutes = calculateTotalMinutes();
    const totalAmount = calculateTotalPrice();
    const timeInfo = updateFinalTime();

    // Update time slot field
    if (selectedDate && selectedTime && timeInfo) {
        const dateStr = selectedDate.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const timeSlotValue = `${formatTimeDisplay(timeInfo.startTime)} - ${formatTimeDisplay(timeInfo.endTime)}, ${dateStr}`;
        document.getElementById('selectedTimeSlot').value = timeSlotValue;
    }

    document.getElementById('totalMinutes').value = totalMinutes;
    document.getElementById('bookingTotalPrice').value = totalAmount.toFixed(2);
    document.getElementById('totalAmount').value = totalAmount.toFixed(2);

    // Generate detailed summary
    const summary = generateDetailedBookingSummary();
    document.getElementById('bookingSummary').value = summary;
}

function generateDetailedBookingSummary() {
    // Get personal details
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const description = document.getElementById('description').value.trim();
    const suburb = document.getElementById('suburb').value;
    const address = document.getElementById('address').value.trim();
    const city = document.getElementById('city').value.trim();
    const postalCode = document.getElementById('postalCode').value.trim();
    const state = document.getElementById('state').value;
    const country = document.getElementById('country').value;

    let summary = "TENNIS LESSON BOOKING DETAILS\n";
    summary += "====================================\n\n";

    summary += "PERSONAL DETAILS\n";
    summary += "-----------------\n";
    summary += `Full Name: ${fullName}\n`;
    summary += `Email: ${email}\n`;
    summary += `Description: ${description || 'N/A'}\n`;
    summary += `Suburb: ${suburb}\n`;
    summary += `Address: ${address}\n`;
    summary += `City: ${city}\n`;
    summary += `Postal Code: ${postalCode}\n`;
    summary += `State: ${state}\n`;
    summary += `Country: ${country}\n\n`;

    const timeInfo = updateFinalTime();
    if (selectedDate && selectedTime && timeInfo) {
        const dateStr = selectedDate.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        summary += `Date: ${dateStr}\n`;
        summary += `Start Time: ${formatTimeDisplay(timeInfo.startTime)}\n`;
        summary += `End Time: ${formatTimeDisplay(timeInfo.endTime)}\n`;
        summary += `Total Duration: ${timeInfo.totalMinutes} minutes\n\n`;
    }

    summary += `Total Lessons: ${lessons.length}\n`;
    summary += `Total Price: ${calculateTotalPrice().toFixed(2)}\n\n`;

    lessons.forEach((lesson, index) => {
        summary += `LESSON ${index + 1} DETAILS\n`;
        summary += `-----------------\n`;
        summary += `Lesson Type: ${lesson.type || 'Not selected'}\n`;
        summary += `Duration: ${lesson.duration || 0} minutes\n`;
        summary += `Number Of Players: ${lesson.players || 0}\n`;
        summary += `Age - Ball Level: ${lesson.ballLevel || 'Not selected'}\n\n`;
    });

    summary += `Booking Created: ${new Date().toLocaleString()}\n`;
    return summary;
}

// Navigation Functions
function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    resetSelection();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    resetSelection();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
    resetSelection();
}

function submitBooking() {
    // Gather all booking details
    const bookingData = {
        fullName: document.getElementById('fullName').value.trim(),
        email: document.getElementById('email').value.trim(),
        description: document.getElementById('description').value.trim(),
        suburb: document.getElementById('suburb').value,
        address: document.getElementById('address').value.trim(),
        city: document.getElementById('city').value.trim(),
        postalCode: document.getElementById('postalCode').value.trim(),
        state: document.getElementById('state').value,
        country: document.getElementById('country').value,
        selectedDate: selectedDate ? formatDate(selectedDate) : null,
        selectedTimeSlot: document.getElementById('selectedTimeSlot').value,
        totalMinutes: document.getElementById('totalMinutes').value,
        bookingTotalPrice: document.getElementById('bookingTotalPrice').value,
        bookingSummary: document.getElementById('bookingSummary').value,
        totalAmount: document.getElementById('totalAmount').value,
        lessons: lessons.map(lesson => ({
            type: lesson.type,
            duration: lesson.duration,
            players: lesson.players,
            ballLevel: lesson.ballLevel
        }))
    };

    // Show loading indicator
    const nextBtn = document.getElementById('nextBtn');
    nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    nextBtn.disabled = true;

    // Send AJAX request to Laravel API
    $.ajax({
        url: 'api/book-appointment',
        type: 'POST',
        data: JSON.stringify(bookingData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // For Laravel CSRF protection
        },
        success: function (response) {
            // Handle successful response
            showToast('Booking confirmed! Thank you for your booking.', 'success');
            resetSelection(); // Reset the form after successful booking
        },
        error: function (xhr) {
            // Handle errors
            let errorMessage = 'Booking failed. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.errors[0]) {
                errorMessage = xhr.responseJSON.errors[0];
            }
            showToast(errorMessage, 'error');
        },
        complete: function () {
            // Reset button state
            nextBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Booking';
            nextBtn.disabled = false;
        }
    });
}

// Initialize Google Places Autocomplete
function initAddressAutocomplete() {
    // Create the autocomplete object
    const addressInput = document.getElementById('address');
    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        types: ['address'],
        // componentRestrictions: {country: 'au'} // Restrict to Australia
    });

    // Listen for when a place is selected
    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        if (!place.address_components) {
            return;
        }

        // Reset all address-related fields
        // document.getElementById('suburb').value = '';
        // document.getElementById('city').value = '';
        // document.getElementById('postalCode').value = '';
        // document.getElementById('state').value = '';
        // document.getElementById('country').value = '';

        // Parse address components
        const addressComponents = place.address_components;
        for (const component of addressComponents) {
            const componentType = component.types[0];

            switch (componentType) {
                case 'locality': // City
                    document.getElementById('city').value = component.long_name;
                    break;
                case 'administrative_area_level_1': // State
                    document.getElementById('state').value = component.long_name;
                    break;
                case 'postal_code': // Postal code
                    document.getElementById('postalCode').value = component.long_name;
                    break;
                    // case 'sublocality_level_1': // Suburb (common in AU)
                    // case 'neighborhood': // Alternative for suburb
                    // document.getElementById('suburb').value = component.long_name;
                    break;
                case 'country': // Country
                    document.getElementById('country').value = component.long_name;
                    break;
            }
        }

        // Special handling for Melbourne suburbs
        if (!document.getElementById('suburb').value) {
            // Try to extract suburb from formatted address
            const formattedAddress = place.formatted_address || '';
            const suburbMatch = formattedAddress.match(/(\b\w+\b)(?= VIC \d{4}, Australia)/i);
            if (suburbMatch && suburbMatch[1]) {
                document.getElementById('suburb').value = suburbMatch[1];
            }
        }
    });
}

function resetSelection() {
    selectedDate = null;
    selectedTime = null;
    lessons = [];
    currentStep = 1;
    lessonIdCounter = 1;

    document.getElementById('timePanel').style.display = 'none';
    document.getElementById('stepperForm').style.display = 'none';
    document.getElementById('noSelection').style.display = 'block';

    // Clear lessons container
    document.getElementById('lessonsContainer').innerHTML = '';

    // Reset stepper UI
    updateStepperUI();

    // Clear personal fields
    document.getElementById('fullName').value = '';
    document.getElementById('email').value = '';
    document.getElementById('description').value = '';
    document.getElementById('suburb').value = '';
    document.getElementById('address').value = '';
    document.getElementById('city').value = '';
    document.getElementById('postalCode').value = '';

    // Clear hidden fields
    document.getElementById('selectedTimeSlot').value = '';
    document.getElementById('totalMinutes').value = '';
    document.getElementById('bookingTotalPrice').value = '';
    document.getElementById('bookingSummary').value = '';
    document.getElementById('totalAmount').value = '';
}


// Toast notification function
function showToast(message, type = 'info', duration = 5000) {
    const toastContainer = document.querySelector('.toast-container');

    // Create unique ID for the toast
    const toastId = 'toast-' + Date.now();

    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast custom-toast toast-${type}`;
    toastEl.id = toastId;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    // Set icon and title based on type
    let iconClass, title;
    switch (type) {
        case 'success':
            iconClass = 'fa-check-circle text-success';
            title = 'Success';
            break;
        case 'error':
            iconClass = 'fa-exclamation-circle text-danger';
            title = 'Error';
            break;
        case 'warning':
            iconClass = 'fa-exclamation-triangle text-warning';
            title = 'Warning';
            break;
        default:
            iconClass = 'fa-info-circle text-info';
            title = 'Information';
    }

    // Toast content
    toastEl.innerHTML = `
                <div class="toast-header toast-header-${type}">
                    <i class="fas ${iconClass} toast-icon"></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;

    // Add toast to container
    toastContainer.appendChild(toastEl);

    // Initialize and show toast
    const toast = new bootstrap.Toast(toastEl, {
        delay: duration,
        autohide: duration > 0
    });

    toast.show();

    // Remove toast from DOM after it hides
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// Demo functions
function showSuccessToast() {
    showToast('Booking confirmed! Thank you for your booking.', 'success');
}

function showErrorToast() {
    showToast('Booking failed. Please check your information and try again.', 'error');
}

function showWarningToast() {
    showToast('Could not find address details. Please try again.', 'warning');
}

function showInfoToast() {
    showToast('Please complete all required fields before proceeding.', 'info');
}


// Initialize calendar on page load
// document.addEventListener('DOMContentLoaded', initCalendar);
document.addEventListener('DOMContentLoaded', function () {
    initCalendar();
    initAddressAutocomplete(); // Add this line
});
