// Initialize settings from backend data
const adminSettings = {
    timeInterval: window.slotDifference || 30,
    dailySchedule: window.availablity || {},
    bookedSlots: window.bookedSlots || {},
    coaches: window.coaches || [],
    coachAvailability: window.coachAvailability || {}
};

// Updated pricing structure
const rateStructure = {
    "Private": { hourly: 105, "45min": 85, "30min": 65 },
    "Semi-Private": { hourly: 60, "45min": 50, "30min": 40 },
    "Group": { hourly: 45, "45min": 35, "30min": 25 },
    "Cardio Tennis": { hourly: 30, "45min": 25, "30min": 20 }
};

const playerLimits = {
    "Private": {min: 1, max: 1},
    "Semi-Private": {min: 2, max: 2},
    "Group": {min: 3, max: 8},
    "Cardio Tennis": {min: 4, max: 10}
};

// Global variables
let currentDate = new Date();
let selectedDate = null;
let selectedTime = null;
let lessons = [];
let currentStep = 1;
let lessonIdCounter = 1;

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUGGING ISSUES ===');
    console.log('Booked slots:', adminSettings.bookedSlots);
    console.log('Coach availability:', adminSettings.coachAvailability);

    // Test the specific booking
    const testDate = '2025-09-01';
    const testTime = '22:00'; // 10:00 PM

    if (adminSettings.bookedSlots[testDate]) {
        console.log('Bookings for Sept 1:', adminSettings.bookedSlots[testDate]);

        // Test booking check
        const isBooked = checkIfTimeSlotBookedForCoach(testTime, testDate, 2);
        console.log(`Is 10:00 PM booked for coach 2? ${isBooked}`);
    }

    initCalendar();
});

function initCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    let startingDay = firstDay.getDay();

    // Adjust for Monday start (Monday = 0, Sunday = 6)
    startingDay = startingDay === 0 ? 6 : startingDay - 1;

    const calendar = document.getElementById('calendar');
    const monthYear = document.getElementById('monthYear');

    if (!calendar || !monthYear) {
        console.error('Calendar elements not found');
        return;
    }

    monthYear.textContent = `${months[month]} ${year}`;
    calendar.innerHTML = '';

    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-date other-month';
        calendar.appendChild(emptyDay);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateElement = createDateElement(date);
        calendar.appendChild(dateElement);
    }
}

function createDateElement(date) {
    const dateElement = document.createElement('div');
    dateElement.className = 'calendar-date';

    const isToday = isDateToday(date);
    const isCurrentMonth = date.getMonth() === currentDate.getMonth();
    const dayOfWeek = date.getDay();
    const isAvailable = adminSettings.dailySchedule[dayOfWeek] !== undefined;
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

    // Add availability indicator for all current month dates
    if (isCurrentMonth) {
        const indicator = document.createElement('div');
        indicator.className = 'availability-indicator';

        if (isAvailable && !isPast) {
            const dateStr = formatDate(date);
            const daySchedule = adminSettings.dailySchedule[dayOfWeek];
            let totalSlots = 0;
            let bookedCount = 0;

            // Calculate total slots
            if (daySchedule && daySchedule.chunks) {
                daySchedule.chunks.forEach(chunk => {
                    const start = timeToMinutes(chunk.startTime);
                    const end = timeToMinutes(chunk.endTime);
                    totalSlots += Math.floor((end - start) / adminSettings.timeInterval);
                });
            }

            // Count booked slots
            if (adminSettings.bookedSlots[dateStr]) {
                if (Array.isArray(adminSettings.bookedSlots[dateStr])) {
                    bookedCount = adminSettings.bookedSlots[dateStr].length;
                } else if (typeof adminSettings.bookedSlots[dateStr] === 'object') {
                    Object.values(adminSettings.bookedSlots[dateStr]).forEach(coachBookings => {
                        if (Array.isArray(coachBookings)) {
                            bookedCount += coachBookings.length;
                        }
                    });
                }
            }

            // Set availability status
            if (totalSlots === 0) {
                indicator.classList.add('unavailable');
                dateElement.classList.add('disabled');
            } else if (bookedCount === 0) {
                indicator.classList.add('available');
            } else if (bookedCount < totalSlots) {
                indicator.classList.add('limited');
            } else {
                indicator.classList.add('fully-booked');
                dateElement.classList.add('disabled');
            }
        } else {
            indicator.classList.add('unavailable');
            if (isPast || !isAvailable) {
                dateElement.classList.add('disabled');
            }
        }

        dateElement.appendChild(indicator);
    }

    // Add click handler
    if (isCurrentMonth && !isPast && isAvailable && !dateElement.classList.contains('disabled')) {
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

function formatDate(date) {
    return date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0');
}

function timeToMinutes(time) {
    if (!time || typeof time !== 'string') {
        console.error('Invalid time:', time);
        return 0;
    }
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

function minutesToTime(minutes) {
    const normalized = ((minutes % 1440) + 1440) % 1440;
    const hours = Math.floor(normalized / 60);
    const mins = normalized % 60;
    return String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
}

function selectDate(date, dateElement) {
    // Remove previous selection
    document.querySelectorAll('.calendar-date.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selection
    dateElement.classList.add('selected');
    selectedDate = date;
    selectedTime = null;

    // Show time slots
    showTimeSlots(date);

    // Hide stepper form
    document.getElementById('stepperForm').style.display = 'none';
    document.getElementById('noSelection').style.display = 'none';
}

function showTimeSlots(date) {
    const timePanel = document.getElementById('timePanel');
    const selectedDateChip = document.getElementById('selectedDateChip');
    const timeSlots = document.getElementById('timeSlots');

    if (!timePanel || !selectedDateChip || !timeSlots) {
        console.error('Time panel elements not found');
        return;
    }

    const dateStr = date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric'
    });

    const dayOfWeek = date.getDay();
    const dateKey = formatDate(date);

    // Get available coaches for this day
    const availableCoaches = [];

    if (adminSettings.coachAvailability) {
        Object.values(adminSettings.coachAvailability).forEach(coach => {
            if (coach.availability[dayOfWeek] && coach.availability[dayOfWeek].chunks) {
                availableCoaches.push(coach);
            }
        });
    }

    if (availableCoaches.length === 0) {
        // Fallback to merged availability
        const daySchedule = adminSettings.dailySchedule[dayOfWeek];
        if (!daySchedule || !daySchedule.chunks) {
            timeSlots.innerHTML = '<div class="text-center p-4">No available times for this day</div>';
            timePanel.style.display = 'block';
            return;
        }

        selectedDateChip.textContent = dateStr + ' - Merged Schedule';
        timeSlots.innerHTML = `
            <div class="text-center p-4">
                <div class="alert alert-info">
                    <strong>Individual Coach Availability Not Set</strong><br>
                    <small>Showing merged schedule. Please set individual availability for each coach.</small>
                </div>
                <div class="mt-3">
                    <h6>Available Times (Merged):</h6>
                    <div class="merged-slots" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px;">
                        ${daySchedule.chunks.map(chunk => {
                            const startMinutes = timeToMinutes(chunk.startTime);
                            const endMinutes = timeToMinutes(chunk.endTime);
                            const bufferMinutes = chunk.bufferMinutes || 0;
                            const availableMinutes = endMinutes - startMinutes - bufferMinutes;
                            let slots = '';
                            for (let minutes = startMinutes; minutes < endMinutes; minutes += adminSettings.timeInterval) {
                                const timeStr = minutesToTime(minutes);
                                const isBooked = checkIfTimeSlotBooked(timeStr, dateKey);

                                // Check if there's enough time for this slot
                                const remainingTime = (startMinutes + availableMinutes) - minutes;
                                const hasEnoughTime = remainingTime >= adminSettings.timeInterval;

                                if (hasEnoughTime) {
                                    slots += `<div class="time-slot ${isBooked ? 'booked' : 'available'}">${formatTimeDisplay(timeStr)}</div>`;
                                } else {
                                    slots += `<div class="time-slot error-slot">${formatTimeDisplay(timeStr)} - Slot not available</div>`;
                                }
                            }
                            return slots;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;
        timePanel.style.display = 'block';
        return;
    }

    // Show individual coach availability
    selectedDateChip.textContent = dateStr + ` - ${availableCoaches.length} Coach${availableCoaches.length !== 1 ? 'es' : ''} Available`;
    timeSlots.innerHTML = '';

    // Create container for all time slots
    const allSlotsContainer = document.createElement('div');
    allSlotsContainer.className = 'coach-slots';

    // Generate time slots for each coach
    availableCoaches.forEach((coach, coachIndex) => {
        coach.availability[dayOfWeek].chunks.forEach(chunk => {
            const startMinutes = timeToMinutes(chunk.startTime);
            const endMinutes = timeToMinutes(chunk.endTime);
            const bufferMinutes = chunk.bufferMinutes || 0;
            const availableMinutes = endMinutes - startMinutes - bufferMinutes;

            console.log(`Buffer minutes for ${coach.name}: ${bufferMinutes}`);

            console.log(`Coach ${coach.name}: Start=${chunk.startTime} (${timeToMinutes(chunk.startTime)}), End=${chunk.endTime} (${timeToMinutes(chunk.endTime)}), Buffer=${bufferMinutes}, Available=${availableMinutes}`);

            for (let minutes = startMinutes; minutes < endMinutes; minutes += adminSettings.timeInterval) {
                const timeStr = minutesToTime(minutes);
                const isBooked = checkIfTimeSlotBookedForCoach(timeStr, dateKey, coach.id);
                const slotEndMinutes = minutes + adminSettings.timeInterval;
                const availableEndMinutes = startMinutes + availableMinutes;

                // Check if there's enough time for this slot (slot duration should be less than remaining time)
                const remainingTime = availableEndMinutes - minutes;
                const hasEnoughTime = remainingTime >= adminSettings.timeInterval;

                console.log(`Time: ${timeStr}, Remaining: ${remainingTime}, HasEnough: ${hasEnoughTime}, Required: ${adminSettings.timeInterval}`);

                if (hasEnoughTime) {
                    console.log(`Creating normal slot for ${timeStr}, Booked: ${isBooked}`);
                    const slot = createTimeSlot(timeStr, isBooked, coach.name, bufferMinutes, coach.id);
                    allSlotsContainer.appendChild(slot);
                } else {
                    console.log(`Creating error slot for ${timeStr} - Slot not available (remaining time: ${remainingTime} < ${adminSettings.timeInterval})`);
                    // Show error for last slot
                    const errorSlot = document.createElement('div');
                    errorSlot.className = 'time-slot error-slot';
                    errorSlot.innerHTML = `
                        <div style="font-weight: 600; font-size: 12px; margin-bottom: 2px;">${formatTimeDisplay(timeStr)}</div>
                        <div style="font-size: 8px; color: #ffc107;">Slot not available</div>
                    `;
                    allSlotsContainer.appendChild(errorSlot);
                }
            }
        });

        // Add separator between coaches
        if (coachIndex < availableCoaches.length - 1) {
            const separator = document.createElement('div');
            separator.style.width = '100%';
            separator.style.height = '15px';
            allSlotsContainer.appendChild(separator);
        }
    });

    timeSlots.appendChild(allSlotsContainer);
    timePanel.style.display = 'block';
}

function checkIfTimeSlotBookedForCoach(timeStr, dateKey, coachId) {
    // Convert coachId to string for comparison (in case it's stored as string in backend)
    const coachIdStr = String(coachId);

    console.log(`Checking booking: ${timeStr} for coach ${coachIdStr} on ${dateKey}`);

    if (!adminSettings.bookedSlots[dateKey] || !adminSettings.bookedSlots[dateKey][coachIdStr]) {
        console.log(`No bookings found for coach ${coachIdStr} on ${dateKey}`);
        return false;
    }

    console.log(`Found bookings:`, adminSettings.bookedSlots[dateKey][coachIdStr]);

    return adminSettings.bookedSlots[dateKey][coachIdStr].some(booking => {
        console.log(`Checking booking:`, booking);

        if (typeof booking === 'string') {
            const isMatch = booking === timeStr;
            console.log(`String match: ${isMatch}`);
            return isMatch;
        } else if (booking && booking.time) {
            // Handle time range (e.g., "10:00 PM - 11:30 PM")
            const timeRange = booking.time;
            console.log(`Time range: ${timeRange}`);

            if (timeRange.includes(' - ')) {
                // Handle time range with date (e.g., "10:00 PM - 11:30 PM, Mon, Sep 1, 2025")
                const timePart = timeRange.split(',')[0]; // Get just the time part
                const [startTime, endTime] = timePart.split(' - ');
                const currentTime = formatTimeForComparison(timeStr);
                const bookingStart = formatTimeForComparison(startTime);
                const bookingEnd = formatTimeForComparison(endTime);

                console.log(`Comparing: ${currentTime} >= ${bookingStart} && ${currentTime} < ${bookingEnd}`);

                // Check if current time slot falls within the booking range
                const isBooked = currentTime >= bookingStart && currentTime < bookingEnd;
                console.log(`Range match: ${isBooked}`);
                return isBooked;
            } else {
                // Single time slot (might also include date)
                const startTime = booking.time.includes(',') ? booking.time.split(',')[0] : booking.time;
                const isMatch = formatTimeForComparison(startTime) === formatTimeForComparison(timeStr);
                console.log(`Single time match: ${isMatch}`);
                return isMatch;
            }
        }
        return false;
    });
}

function checkIfTimeSlotBooked(timeStr, dateKey) {
    if (!adminSettings.bookedSlots[dateKey]) return false;

    if (Array.isArray(adminSettings.bookedSlots[dateKey])) {
        return adminSettings.bookedSlots[dateKey].some(booking => {
            if (typeof booking === 'string') {
                return booking === timeStr;
            } else if (booking && booking.time) {
                // Handle time range (e.g., "10:00 PM - 11:30 PM")
                const timeRange = booking.time;
                if (timeRange.includes(' - ')) {
                    const [startTime, endTime] = timeRange.split(' - ');
                    const currentTime = formatTimeForComparison(timeStr);
                    const bookingStart = formatTimeForComparison(startTime);
                    const bookingEnd = formatTimeForComparison(endTime);

                    // Check if current time slot falls within the booking range
                    return currentTime >= bookingStart && currentTime < bookingEnd;
                } else {
                    // Single time slot
                    const startTime = booking.time;
                    return formatTimeForComparison(startTime) === formatTimeForComparison(timeStr);
                }
            }
            return false;
        });
    } else if (typeof adminSettings.bookedSlots[dateKey] === 'object') {
        for (const coachId in adminSettings.bookedSlots[dateKey]) {
            const coachBookings = adminSettings.bookedSlots[dateKey][coachId];
            if (Array.isArray(coachBookings)) {
                const isBooked = coachBookings.some(booking => {
                    if (typeof booking === 'string') {
                        return booking === timeStr;
                    } else if (booking && booking.time) {
                        // Handle time range (e.g., "10:00 PM - 11:30 PM")
                        const timeRange = booking.time;
                        if (timeRange.includes(' - ')) {
                            // Handle time range with date (e.g., "10:00 PM - 11:30 PM, Mon, Sep 1, 2025")
                            const timePart = timeRange.split(',')[0]; // Get just the time part
                            const [startTime, endTime] = timePart.split(' - ');
                            const currentTime = formatTimeForComparison(timeStr);
                            const bookingStart = formatTimeForComparison(startTime);
                            const bookingEnd = formatTimeForComparison(endTime);

                            // Check if current time slot falls within the booking range
                            return currentTime >= bookingStart && currentTime < bookingEnd;
                        } else {
                            // Single time slot (might also include date)
                            const startTime = booking.time.includes(',') ? booking.time.split(',')[0] : booking.time;
                            return formatTimeForComparison(startTime) === formatTimeForComparison(timeStr);
                        }
                    }
                    return false;
                });
                if (isBooked) return true;
            }
        }
    }

    return false;
}

function formatTimeForComparison(timeStr) {
    // Handle both 24-hour format (22:00) and 12-hour format (10:00 PM)
    if (timeStr.includes(' ')) {
        // 12-hour format with AM/PM
        const [time, period] = timeStr.split(' ');
        let [hours, minutes] = time.split(':').map(Number);

        if (period === 'PM' && hours !== 12) {
            hours += 12;
        } else if (period === 'AM' && hours === 12) {
            hours = 0;
        }

        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    } else {
        // 24-hour format (already in correct format)
        return timeStr;
    }
}

function createTimeSlot(time, isBooked, coachName = null, bufferMinutes = 0, coachId = null) {
    const slot = document.createElement('div');
    slot.className = 'time-slot';

    const timeText = formatTimeDisplay(time);

    if (coachName) {
        slot.innerHTML = `
            <div style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">${timeText}</div>
            <div style="font-size: 10px; color: #666;">${coachName}</div>
        `;

        slot.dataset.coachName = coachName;
        slot.dataset.coachId = coachId;
        slot.dataset.bufferMinutes = bufferMinutes;
    } else {
        slot.textContent = timeText;
    }

    if (isBooked) {
        slot.classList.add('booked');
    } else {
        slot.classList.add('available');
        slot.addEventListener('click', () => selectTime(time, slot, coachName, bufferMinutes, coachId));
    }

    return slot;
}

function formatTimeDisplay(time) {
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
    return `${displayHours}:${String(minutes).padStart(2, '0')} ${period}`;
}

function selectTime(time, element, coachName = null, bufferMinutes = 0, coachId = null) {
    // Remove previous selection
    document.querySelectorAll('.time-slot.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selection
    element.classList.add('selected');
    selectedTime = time;

    // Store selected coach
    if (coachName) {
        window.selectedCoachName = coachName;
        window.selectedCoachId = coachId;
        window.selectedBufferMinutes = bufferMinutes;
    }

    // Show stepper form
    document.getElementById('stepperForm').style.display = 'block';
    document.getElementById('noSelection').style.display = 'none';

    // Initialize with first lesson
    if (lessons.length === 0) {
        addNewLesson();
    }

    updateAllCalculations();
}

function addNewLesson() {
    const lesson = {
        id: lessonIdCounter++,
        type: 'Private',
        duration: 30,
        players: 1
    };
    lessons.push(lesson);
    renderLessons();
    updateAllCalculations();
}

function removeLesson(lessonId) {
    lessons = lessons.filter(lesson => lesson.id !== lessonId);
    renderLessons();
    updateAllCalculations();
}

function renderLessons() {
    const lessonsContainer = document.getElementById('lessonsContainer');
    if (!lessonsContainer) return;

    lessonsContainer.innerHTML = '';

    lessons.forEach((lesson, index) => {
        const lessonElement = document.createElement('div');
        lessonElement.className = 'lesson-item';
        lessonElement.innerHTML = `
            <div class="lesson-header">
                <h6>Lesson ${index + 1}</h6>
                ${lessons.length > 1 ? `<button type="button" class="btn-remove-lesson" onclick="removeLesson(${lesson.id})">
                    <i class="fas fa-trash"></i>
                </button>` : ''}
            </div>
            <div class="lesson-content">
                <div class="form-group">
                    <label>Lesson Type</label>
                    <select class="form-control lesson-type" onchange="updateLesson(${lesson.id}, 'type', this.value)">
                        <option value="Private" ${lesson.type === 'Private' ? 'selected' : ''}>Private</option>
                        <option value="Semi-Private" ${lesson.type === 'Semi-Private' ? 'selected' : ''}>Semi-Private</option>
                        <option value="Group" ${lesson.type === 'Group' ? 'selected' : ''}>Group</option>
                        <option value="Cardio Tennis" ${lesson.type === 'Cardio Tennis' ? 'selected' : ''}>Cardio Tennis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duration</label>
                    <select class="form-control lesson-duration" onchange="updateLesson(${lesson.id}, 'duration', parseInt(this.value))">
                        <option value="30" ${lesson.duration === 30 ? 'selected' : ''}>30 minutes</option>
                        <option value="45" ${lesson.duration === 45 ? 'selected' : ''}>45 minutes</option>
                        <option value="60" ${lesson.duration === 60 ? 'selected' : ''}>1 hour</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Number of Players</label>
                    <input type="number" class="form-control lesson-players"
                           value="${lesson.players}" min="${playerLimits[lesson.type].min}"
                           max="${playerLimits[lesson.type].max}"
                           onchange="updateLesson(${lesson.id}, 'players', parseInt(this.value))">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control lesson-description"
                              placeholder="Enter lesson description..."
                              onchange="updateLesson(${lesson.id}, 'description', this.value)">${lesson.description || ''}</textarea>
                </div>
                <div class="lesson-price">
                    <strong>Price: $${calculateLessonPrice(lesson)}</strong>
                </div>
            </div>
        `;
        lessonsContainer.appendChild(lessonElement);
    });
}

function updateLesson(lessonId, field, value) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (lesson) {
        lesson[field] = value;

        if (field === 'type') {
            const limits = playerLimits[value];
            lesson.players = Math.max(limits.min, Math.min(limits.max, lesson.players));
        }

        renderLessons();
        updateAllCalculations();
    }
}

function calculateLessonPrice(lesson) {
    const basePrice = rateStructure[lesson.type][lesson.duration === 60 ? 'hourly' : lesson.duration + 'min'];
    return basePrice * lesson.players;
}

function updateAllCalculations() {
    const totalPrice = lessons.reduce((sum, lesson) => sum + calculateLessonPrice(lesson), 0);
    const totalDuration = lessons.reduce((sum, lesson) => sum + lesson.duration, 0);

    const totalPriceElement = document.getElementById('totalPrice');
    const totalDurationElement = document.getElementById('totalDuration');
    const summaryTotalPriceElement = document.getElementById('summaryTotalPrice');

    if (totalPriceElement) totalPriceElement.textContent = `$${totalPrice}`;
    if (totalDurationElement) totalDurationElement.textContent = `${totalDuration} minutes`;
    if (summaryTotalPriceElement) summaryTotalPriceElement.textContent = `Total: $${totalPrice}`;

    if (currentStep === 2) {
        generateSummary();
    }
}

function nextStep() {
    if (currentStep === 1) {
        if (lessons.length === 0) {
            alert('Please add at least one lesson.');
            return;
        }

        if (!selectedTime) {
            alert('Please select a time slot.');
            return;
        }

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('submitBooking').style.display = 'inline-block';
        document.getElementById('prevBtn').style.display = 'inline-block';
        currentStep = 2;

        generateSummary();
    }
}

function prevStep() {
    if (currentStep === 2) {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        document.getElementById('nextBtn').style.display = 'inline-block';
        document.getElementById('submitBooking').style.display = 'none';
        document.getElementById('prevBtn').style.display = 'none';
        currentStep = 1;
    }
}

function generateSummary() {
    const summaryContainer = document.getElementById('bookingSummary');
    if (!summaryContainer) return;

    const totalPrice = lessons.reduce((sum, lesson) => sum + calculateLessonPrice(lesson), 0);
    const totalDuration = lessons.reduce((sum, lesson) => sum + lesson.duration, 0);

    const dateStr = selectedDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    let summaryHTML = `
        <div class="summary-section">
            <h6><i class="fas fa-calendar"></i> Date & Time</h6>
            <p>${dateStr} at ${formatTimeDisplay(selectedTime)}</p>
            ${window.selectedCoachName ? `<p><strong>Coach:</strong> ${window.selectedCoachName}</p>` : ''}
        </div>

        <div class="summary-section">
            <h6><i class="fas fa-list"></i> Lessons</h6>
            ${lessons.map((lesson, index) => `
                <div class="lesson-summary">
                    <strong>Lesson ${index + 1}:</strong> ${lesson.type} - ${lesson.duration} minutes - ${lesson.players} player${lesson.players !== 1 ? 's' : ''}
                    <span class="lesson-price">$${calculateLessonPrice(lesson)}</span>
                </div>
            `).join('')}
        </div>

        <div class="summary-section">
            <h6><i class="fas fa-calculator"></i> Total</h6>
            <p><strong>Duration:</strong> ${totalDuration} minutes</p>
            <p><strong>Total Price:</strong> $${totalPrice}</p>
        </div>
    `;

    summaryContainer.innerHTML = summaryHTML;
}

function submitBooking() {
    const formData = {
        selectedDate: formatDate(selectedDate),
        selectedTimeSlot: selectedTime,
        lessons: lessons,
        selectedCoachId: window.selectedCoachId || null,
        selectedCoachName: window.selectedCoachName || null,
        selectedBufferMinutes: window.selectedBufferMinutes || 0,
        playerType: 'Regular'
    };

    const submitBtn = document.getElementById('submitBooking');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    fetch('/book-appointment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_free_trial) {
                window.location.href = 'https://homecourtadvantage-net.beast-hosting.com/success';
            } else {
                window.location.href = data.url;
            }
        } else {
            alert('Error: ' + (data.message || 'Something went wrong'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Something went wrong');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function resetSelection() {
    selectedDate = null;
    selectedTime = null;
    lessons = [];
    currentStep = 1;
    lessonIdCounter = 1;

    document.querySelectorAll('.calendar-date.selected').forEach(el => {
        el.classList.remove('selected');
    });
    document.querySelectorAll('.time-slot.selected').forEach(el => {
        el.classList.remove('selected');
    });

    document.getElementById('timePanel').style.display = 'none';
    document.getElementById('stepperForm').style.display = 'none';
    document.getElementById('noSelection').style.display = 'block';

    if (currentStep === 2) {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    }

    renderLessons();
}

// Navigation functions
function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
}
