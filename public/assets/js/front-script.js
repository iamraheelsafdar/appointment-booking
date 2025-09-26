// Initialize settings from backend data
const adminSettings = {
    timeInterval: window.slotDifference || 30,
    dailySchedule: window.availablity || {},
    bookedSlots: window.bookedSlots || {},
    coaches: window.coaches || [],
    coachAvailability: window.coachAvailability || {}
};

// Tennis lesson pricing
const rateStructure = {
    "Private": {
        base: 85, // Base price for 30 minutes
        perDuration: 20 // Add $20 for each additional 30-minute increment
    },
    "Semi-Private": {
        base: 100, // Base price for 30 minutes
        perDuration: 20 // Add $20 for each additional 30-minute increment
    },
    "Group": {
        base: 45, // Base for 3 players
        perPlayer: 10 // Add $10 for each additional player above 3
    },
    "Cardio Tennis": {
        perPlayer: 20 // $20 per person
    }
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
    initCalendar();

    // Initialize player type controls
    const initialType = document.getElementById('playerType')?.value || 'Returning';
    onPlayerTypeChange(initialType);
});

// Google Maps API callback function
function initGoogleMaps() {
    initAddressAutocomplete();
}

// Initialize Google Places Autocomplete
function initAddressAutocomplete() {
    const addressInput = document.getElementById('address');
    if (!addressInput) return;

    // Define Melbourne bounds
    const melbourneBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(-37.9, 144.8), // Southwest corner
        new google.maps.LatLng(-37.6, 145.2)  // Northeast corner
    );

    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        types: ['address'],
        componentRestrictions: {country: 'AU'},
        bounds: melbourneBounds,
        strictBounds: true,
        fields: ['address_components', 'formatted_address', 'geometry']
    });

    // Listen for place selection
    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();

        if (!place.address_components) {
            return;
        }

        // Check if within Melbourne area
        if (place.geometry && place.geometry.location) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            if (lat < -37.9 || lat > -37.6 || lng < 144.8 || lng > 145.2) {
                showToast('warning', 'Location Error', 'Please select an address within Melbourne area only.');
                addressInput.value = '';
                return;
            }
        }

        // Set default values
        document.getElementById('suburb').value = 'Toorak';
        document.getElementById('city').value = 'Melbourne';
        document.getElementById('state').value = 'Victoria';
        document.getElementById('country').value = 'Australia';

        // Parse postal code
        const addressComponents = place.address_components;
        for (const component of addressComponents) {
            if (component.types[0] === 'postal_code') {
                document.getElementById('postalCode').value = component.long_name;
                break;
            }
        }

        // Validate postal code
        const postalCode = document.getElementById('postalCode').value;
        if (postalCode && (parseInt(postalCode) < 3000 || parseInt(postalCode) > 3999)) {
            showToast('warning', 'Location Error', 'Please select an address within Melbourne area only.');
            addressInput.value = '';
            document.getElementById('postalCode').value = '';
        }
    });
}

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
                                const hasEnoughTime = remainingTime > adminSettings.timeInterval;

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



        for (let minutes = startMinutes; minutes < endMinutes; minutes += adminSettings.timeInterval) {
            const timeStr = minutesToTime(minutes);
                const isBooked = checkIfTimeSlotBookedForCoach(timeStr, dateKey, coach.id);
                const slotEndMinutes = minutes + adminSettings.timeInterval;
                const availableEndMinutes = startMinutes + availableMinutes;

                // Check if there's enough time for this slot (slot duration should be less than remaining time)
                const remainingTime = availableEndMinutes - minutes;
                const hasEnoughTime = remainingTime > adminSettings.timeInterval;

                if (hasEnoughTime) {
                    const slot = createTimeSlot(timeStr, isBooked, coach.name, bufferMinutes, coach.id, chunk.startTime, chunk.endTime);
                    allSlotsContainer.appendChild(slot);
                } else {
                    // Show error for last slot
                    const errorSlot = document.createElement('div');
                    errorSlot.className = 'time-slot error-slot';
                    errorSlot.innerHTML = `
                        <div style="font-weight: 600; font-size: 12px; margin-bottom: 2px;">${formatTimeDisplay(timeStr)}</div>
                        <div style="font-size: 10px; color: #856404;">Slot not available</div>
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

    if (!adminSettings.bookedSlots[dateKey] || !adminSettings.bookedSlots[dateKey][coachIdStr]) {
        return false;
    }

    return adminSettings.bookedSlots[dateKey][coachIdStr].some(booking => {
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

function createTimeSlot(time, isBooked, coachName = null, bufferMinutes = 0, coachId = null, startTime = null, endTime = null) {
    const slot = document.createElement('div');
    slot.className = 'time-slot';

    const timeText = formatTimeDisplay(time);

    if (coachName) {
        slot.innerHTML = `
            <div class="time-slot-time" style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">${timeText}</div>
            <div style="font-size: 10px; color: #fff; background-color: #000; border-radius: 10px;">${coachName}</div>
        `;

        slot.dataset.coachName = coachName;
        slot.dataset.coachId = coachId;
        slot.dataset.bufferMinutes = bufferMinutes;
        slot.dataset.startTime = startTime;
        slot.dataset.endTime = endTime;
    } else {
        slot.textContent = timeText;
    }

    if (isBooked) {
        slot.classList.add('booked');
    } else {
        slot.classList.add('available');
        slot.addEventListener('click', () => selectTime(time, slot, coachName, bufferMinutes, coachId, startTime, endTime));
    }

    return slot;
}

function formatTimeDisplay(time) {
    const [hours, minutes] = time.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
    return `${displayHours}:${String(minutes).padStart(2, '0')} ${period}`;
}

// Enhanced selectTime function with immediate validation
function selectTime(time, element, coachName = null, bufferMinutes = 0, coachId = null, startTime = null, endTime = null) {
    // Remove previous selection
    document.querySelectorAll('.time-slot.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Add selection
    element.classList.add('selected');
    selectedTime = time;

    // Store selected coach and slot information
    if (coachName) {
        window.selectedCoachName = coachName;
        window.selectedCoachId = coachId;
        window.selectedBufferMinutes = bufferMinutes;
        window.selectedStartTime = startTime;
        window.selectedEndTime = endTime;

        // Create the full time slot string for validation
        if (startTime && endTime) {
            const startTimeFormatted = formatTimeDisplay(startTime);
            const endTimeFormatted = formatTimeDisplay(endTime);
            window.selectedTimeSlot = `${startTimeFormatted} - ${endTimeFormatted}`;
        } else {
            window.selectedTimeSlot = formatTimeDisplay(time);
        }
    }

    // Show stepper form
    document.getElementById('stepperForm').style.display = 'block';
    document.getElementById('noSelection').style.display = 'none';

    // Initialize with first lesson if none exist
    if (lessons.length === 0) {
        const lesson = {
            id: lessonIdCounter++,
            type: 'Private',
            duration: adminSettings.timeInterval,
            players: 1,
            ballLevel: '',
            description: ''
        };
        lessons.push(lesson);
        renderLessons();
    }

    // Force update of all calculations to refresh time validation
    updateAllCalculations();

    // Immediate validation feedback
    const validation = validateLessonDuration();
    if (validation.valid) {
        showToast('success', 'Time Slot Selected', `Selected time slot with ${validation.availableTime} minutes available.`);
    }
}

// Updated addNewLesson function with time validation
function addNewLesson() {
    // Create a temporary lesson to test validation
    const tempLesson = {
        id: lessonIdCounter,
        type: 'Private',
        duration: adminSettings.timeInterval,
        players: 1,
        ballLevel: '',
        description: ''
    };

    // Add temporarily to lessons array for validation
    lessons.push(tempLesson);

    // Validate the new total time
    const validation = validateLessonDuration();

    if (!validation.valid) {
        // Remove the temporary lesson
        lessons.pop();

        showToast('warning', 'Cannot Add Lesson', validation.message);
        return;
    }

    // If validation passes, keep the lesson and increment counter
    tempLesson.id = lessonIdCounter++;

    renderLessons();
    updateAllCalculations();

    if (validation.remaining > 0) {
        showToast('success', 'Lesson Added', `Lesson added successfully. ${validation.remaining} minutes remaining in slot.`);
    } else {
        showToast('info', 'Lesson Added', 'Lesson added successfully. Time slot is now fully utilized.');
    }
}

function calculateAvailableTimeForSlot() {
    if (!selectedTime || !selectedDate) {
        return 60; // Default fallback
    }

    // Get the time slots container to analyze available slots
     const timeSlotsContainer = document.getElementById('timeSlots');
     if (!timeSlotsContainer) {
         return 60;
     }

         // Find all available time slots for the SELECTED COACH only
     const allTimeSlots = timeSlotsContainer.querySelectorAll('.time-slot');
     const availableSlots = [];

     // Get the selected coach name from the selected slot
     const selectedSlotElement = allTimeSlots[selectedTime] || document.querySelector('.time-slot.selected');
     const selectedCoachName = selectedSlotElement ? selectedSlotElement.querySelector('div[style*="font-size: 10px"]')?.textContent : null;

     allTimeSlots.forEach((slot, index) => {
         const timeText = slot.querySelector('.time-slot-time')?.textContent || slot.textContent || '';
         const coachName = slot.querySelector('div[style*="font-size: 10px"]')?.textContent || '';
         const isAvailable = !slot.classList.contains('error-slot') && !slot.classList.contains('booked');
         const isSelected = slot.classList.contains('selected');

         // Only include slots from the SELECTED COACH
         const isFromSelectedCoach = coachName === selectedCoachName;

         if ((isAvailable || isSelected) && isFromSelectedCoach) {
             // Parse the time from the slot
             const timeMatch = timeText.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
             if (timeMatch) {
                 let hours = parseInt(timeMatch[1]);
                 const minutes = parseInt(timeMatch[2]);
                 const period = timeMatch[3].toUpperCase();

                 // Convert to 24-hour format
                 if (period === 'PM' && hours !== 12) hours += 12;
                 if (period === 'AM' && hours === 12) hours = 0;

                 const timeIn24Hour = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');

                 availableSlots.push({
                     index: index,
                     displayTime: timeText.trim(),
                     time24: timeIn24Hour,
                     isSelected: isSelected,
                     element: slot,
                     coachName: coachName
                 });
             }
         }
     });

         // Find the selected slot
     const selectedSlotInfo = availableSlots.find(slot => slot.isSelected);
     if (!selectedSlotInfo) {
         return 60;
     }

          // Count consecutive slots starting from the selected slot
     const selectedIndex = selectedSlotInfo.index;
     let consecutiveSlots = 0;
     const slotInterval = adminSettings.timeInterval || 30;

     // Count how many consecutive available slots we have from the selected slot
     for (let i = 0; i < availableSlots.length; i++) {
         const currentSlot = availableSlots[i];

         // Only count slots that come at or after the selected slot index
         if (currentSlot.index >= selectedIndex) {
             consecutiveSlots++;
         }
     }

         // Calculate available time
     const totalAvailableTime = consecutiveSlots * slotInterval;
     const bufferMinutes = window.selectedBufferMinutes || 0;
     const finalAvailableTime = Math.max(0, totalAvailableTime - bufferMinutes);

     return finalAvailableTime;
}

function removeLesson(lessonId) {
    if (lessons.length > 1) {
        lessons = lessons.filter(l => l.id !== lessonId);

        const lessonCard = document.querySelector(`[data-lesson-id="${lessonId}"]`);
        if (lessonCard) lessonCard.remove();

        updateLessonTitles();
        updateRemoveButtons();
        updateAllCalculations();
    }
}

function updateLessonTitles() {
    document.querySelectorAll('.lesson-card').forEach((card, index) => {
        const title = card.querySelector('.lesson-title');
        if (title) title.textContent = `Lesson ${index + 1}`;
    });
}

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-lesson-btn');
    removeButtons.forEach(btn => {
        btn.style.display = (lessons.length > 1) ? 'block' : 'none';
    });
}

// Enhanced validation for step 1
function validateStep1() {
    let isValid = true;
    const errorMessages = [];

    // Existing validation...
    const fullName = document.getElementById('fullName').value.trim();
    const email = document.getElementById('email').value.trim();
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
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

    if (!phoneNumber) {
        errorMessages.push('Phone number is required');
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
    }

    // Validate lesson details
    lessons.forEach((lesson, index) => {
        if (!lesson.type || !lesson.duration || !lesson.ballLevel) {
            errorMessages.push(`Please complete all details for Lesson ${index + 1}`);
            isValid = false;
        }
    });

    // Free trial duration validation
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';
    if (isFreeTrial) {
        const totalDuration = lessons.reduce((sum, lesson) => sum + (lesson.duration || 0), 0);
        if (totalDuration > 60) {
            errorMessages.push(`Free trial players cannot book more than 60 minutes total duration. Current total: ${totalDuration} minutes.`);
            isValid = false;
        }
    }

    // Time validation
    const timeValidation = validateLessonDuration();
    if (!timeValidation.valid) {
        errorMessages.push(timeValidation.message);
        isValid = false;
    }

    if (!isValid) {
        showToast('danger', 'Validation Error', `Please fix the following errors:<br>• ${errorMessages.join('<br>• ')}`);
    }

    return isValid;
}

// Add CSS for enhanced time validation display
const additionalCSS = `
<style>
.time-validation-display {
    margin: 15px 0;
    border-radius: 8px;
    overflow: hidden;
}

.time-validation-display.alert-success .alert {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.time-validation-display.alert-warning .alert {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.time-validation-display.alert-danger .alert {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.time-breakdown {
    font-size: 14px;
    line-height: 1.6;
}

.coach-info {
    margin-top: 8px;
    font-size: 12px;
    opacity: 0.8;
}

.lesson-summary .lesson-price {
    color: #28a745;
    font-weight: 600;
    float: right;
}

.pricing-breakdown {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    font-size: 12px;
}
</style>
`;

// Add the CSS to the document head
document.head.insertAdjacentHTML('beforeend', additionalCSS);

function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}


function onPlayerTypeChange(val) {
    const isFreeTrial = (val === 'FreeTrial');


    // Toggle the Add Lesson button
    const addBtn = document.getElementById('addLessonBtn');
    if (addBtn) addBtn.style.display = isFreeTrial ? 'none' : 'inline-flex';

    // Show/hide free trial info message
    const freeTrialInfo = document.getElementById('freeTrialInfo');
    if (freeTrialInfo) {
        freeTrialInfo.style.display = isFreeTrial ? 'block' : 'none';
        freeTrialInfo.style.fontSize = isFreeTrial ? '12px' : '0';
        freeTrialInfo.style.position = isFreeTrial ? 'absolute' : 'relative';
    }

    // Enforce a single lesson for Free Trial
    if (isFreeTrial && lessons.length > 1) {
        lessons = [lessons[0]];
        // Remove extra lesson cards in the DOM
        const cards = document.querySelectorAll('.lesson-card');
        cards.forEach((card, idx) => {
            if (idx > 0) card.remove();
        });
        updateLessonTitles();
    updateAllCalculations();
        showToast('info', 'Free Trial Mode', 'Free Trial players can only book one lesson and are limited to 60 minutes total duration.');
    }

    // Re-render lessons to update duration options
    renderLessons();

    // Update remove buttons visibility
    updateRemoveButtons();
}

function renderLessons() {
    const lessonsContainer = document.getElementById('lessonsContainer');
    if (!lessonsContainer) return;

    lessonsContainer.innerHTML = '';

    lessons.forEach((lesson, index) => {
        const lessonElement = document.createElement('div');
        lessonElement.className = 'lesson-card';
        lessonElement.dataset.lessonId = lesson.id;
        lessonElement.innerHTML = `
                <div class="lesson-header">
                <div class="lesson-title">Lesson ${index + 1}</div>
                ${lessons.length > 1 ? `<button type="button" class="remove-lesson-btn" onclick="removeLesson(${lesson.id})">
                        <i class="fas fa-times"></i> Remove
                </button>` : ''}
                </div>
                <div class="lesson-grid">
                    <div class="form-group">
                        <label class="form-label">Lesson Type</label>
                    <select class="form-control lesson-type" onchange="updateLesson(${lesson.id}, 'type', this.value)">
                            <option value="">Select Type</option>
                        <option value="Private" ${lesson.type === 'Private' ? 'selected' : ''}>Private</option>
                        <option value="Semi-Private" ${lesson.type === 'Semi-Private' ? 'selected' : ''}>Semi-Private</option>
                        <option value="Group" ${lesson.type === 'Group' ? 'selected' : ''}>Group</option>
                        <option value="Cardio Tennis" ${lesson.type === 'Cardio Tennis' ? 'selected' : ''}>Cardio Tennis</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                    <select class="form-control lesson-duration" onchange="updateLesson(${lesson.id}, 'duration', parseInt(this.value))">
                            ${generateDurationOptions(lesson.duration)}
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Number of Players</label>
                    <select class="form-control lesson-players" onchange="updateLesson(${lesson.id}, 'players', parseInt(this.value))">
                            ${generatePlayerOptions(lesson.type, lesson.players)}
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age - Ball Level</label>
                    <select class="form-control lesson-ball" onchange="updateLesson(${lesson.id}, 'ballLevel', this.value)">
                            <option value="">Select Age - Ball Level</option>
                        <option value="Red" ${lesson.ballLevel === 'Red' ? 'selected' : ''}>3-8 Years Old - Red Ball</option>
                        <option value="Orange" ${lesson.ballLevel === 'Orange' ? 'selected' : ''}>9-10 Years Old - Orange Ball</option>
                        <option value="Green" ${lesson.ballLevel === 'Green' ? 'selected' : ''}>11-12 Years Old - Green Ball</option>
                        <option value="Yellow" ${lesson.ballLevel === 'Yellow' ? 'selected' : ''}>13+ Years Old - Yellow Ball</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lesson Description (Optional)</label>
                        <textarea class="form-control lesson-description"
                              onchange="updateLesson(${lesson.id}, 'description', this.value)"
                              placeholder="Any special requirements for lesson"
                              rows="1">${lesson.description || ''}</textarea>
                    </div>
                </div>
            `;
        lessonsContainer.appendChild(lessonElement);
    });
    updateRemoveButtons();
}

// Updated updateLesson function with time validation
function updateLesson(lessonId, field, value) {
    const lesson = lessons.find(l => l.id === lessonId);
    if (!lesson) return;

    // Store original value for rollback
    const originalValue = lesson[field];

    // Temporarily update the lesson
    lesson[field] = value;

    // If updating duration, validate it doesn't exceed available time
    if (field === 'duration') {
        // Check free trial duration limit first
        const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';
        if (isFreeTrial) {
            const totalDuration = lessons.reduce((sum, l) => {
                if (l.id === lessonId) {
                    return sum + value; // Use new value for current lesson
                }
                return sum + (l.duration || 0);
            }, 0);

            if (totalDuration > 60) {
                // Rollback the change
                lesson[field] = originalValue;

                showToast('warning', 'Duration Limit Exceeded', `Free trial players cannot exceed 60 minutes total duration. Current total would be: ${totalDuration} minutes.`);

                // Reset the form field to original value
                const durationSelect = document.querySelector(`[onchange*="updateLesson(${lessonId}, 'duration'"]`);
                if (durationSelect) {
                    durationSelect.value = originalValue;
                }

                return;
            }
        }

        const validation = validateLessonDuration();

        if (!validation.valid) {
            // Rollback the change
            lesson[field] = originalValue;

            showToast('warning', 'Duration Too Long', validation.message);

            // Reset the form field to original value
            const durationSelect = document.querySelector(`[onchange*="updateLesson(${lessonId}, 'duration'"]`);
            if (durationSelect) {
                durationSelect.value = originalValue;
            }

            return;
        }
    }

    // Update player options based on lesson type
    if (field === 'type') {
        const limits = playerLimits[value];
        if (limits) {
            lesson.players = Math.max(limits.min, Math.min(lesson.players || limits.min, limits.max));
        }
    }

    renderLessons();
    updateAllCalculations();
}

function generateDurationOptions(selectedDuration) {
    let options = '';
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';
    const maxDuration = isFreeTrial ? 60 : 120;

    for (let i = adminSettings.timeInterval; i <= maxDuration; i += adminSettings.timeInterval) {
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

function calculateLessonPrice(lesson) {
    if (!lesson.type || !rateStructure[lesson.type]) {
        return 0;
    }

    const rates = rateStructure[lesson.type];
    let price = 0;

    switch (lesson.type) {
        case "Private":
            // Base price + (duration increments * per duration cost)
            const privateDurationIncrements = Math.ceil(lesson.duration / adminSettings.timeInterval);
            price = rates.base + (rates.perDuration * (privateDurationIncrements - 1));
            break;

        case "Semi-Private":
            // Base price + (duration increments * per duration cost)
            const semiPrivateDurationIncrements = Math.ceil(lesson.duration / adminSettings.timeInterval);
            price = rates.base + (rates.perDuration * (semiPrivateDurationIncrements - 1));
            break;

        case "Group":
            // Base price for 3 players + additional cost for extra players
            const extraPlayers = Math.max(0, lesson.players - 3);
            price = rates.base + (extraPlayers * rates.perPlayer);
            break;

        case "Cardio Tennis":
            // $20 per player
            price = rates.perPlayer * lesson.players;
            break;

        default:
            price = 0;
    }

    return Math.max(0, price);
}

function updateAllCalculations() {
    const totalPrice = lessons.reduce((sum, lesson) => sum + calculateLessonPrice(lesson), 0);
    const totalDuration = lessons.reduce((sum, lesson) => sum + lesson.duration, 0);
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';

    const totalPriceElement = document.getElementById('totalPrice');
    const totalDurationElement = document.getElementById('totalDuration');
    const summaryTotalPriceElement = document.getElementById('summaryTotalPrice');

    if (totalPriceElement) totalPriceElement.textContent = isFreeTrial ? 'FREE TRIAL' : `$${totalPrice}`;
    if (totalDurationElement) totalDurationElement.textContent = `${totalDuration} minutes`;
    if (summaryTotalPriceElement) summaryTotalPriceElement.textContent = isFreeTrial ? 'Total: FREE TRIAL' : `Total: $${totalPrice}`;

    // Update time validation display
    updateTimeValidationDisplay(totalDuration);



    if (currentStep === 2) {
        generateSummary();
    }
}

// Enhanced time validation function
function validateLessonDuration() {
    if (!selectedTime || !selectedDate) {
        return { valid: true, message: '', availableTime: 0, totalTime: 0, remaining: 0 };
    }

    const totalLessonTime = lessons.reduce((sum, lesson) => sum + (lesson.duration || 0), 0);
         const availableTime = calculateAvailableTimeForSlot();

    if (totalLessonTime > availableTime) {
        const exceeded = totalLessonTime - availableTime;
        return {
            valid: false,
            message: `Total lesson time (${totalLessonTime} minutes) exceeds available slot time (${availableTime} minutes). You need ${exceeded} fewer minutes.`,
            totalTime: totalLessonTime,
            availableTime: availableTime,
            exceeded: exceeded,
            remaining: 0
        };
    }

    const remaining = availableTime - totalLessonTime;
    return {
        valid: true,
        message: `${remaining} minutes remaining in selected time slot`,
        totalTime: totalLessonTime,
        availableTime: availableTime,
        remaining: remaining,
        exceeded: 0
    };
}

function updateTimeValidationDisplay(totalDuration) {
     const timeValidationDisplay = document.getElementById('timeValidationDisplay');
     const timeValidationText = document.getElementById('timeValidationText');

     if (!timeValidationDisplay || !timeValidationText) {
         return;
     }

    if (selectedTime && selectedDate) {
        const validation = validateLessonDuration();

        // Show the display
        timeValidationDisplay.style.display = 'block';

        let displayClass = 'alert-info';
        let statusText = '';
        let statusIcon = '';

        if (!validation.valid) {
            displayClass = 'alert-danger p-2';
            statusIcon = '⚠️';
            statusText = `EXCEEDED: Need ${validation.exceeded} fewer minutes`;
        } else if (validation.remaining <= 0) {
            displayClass = 'alert-warning p-2';
            statusIcon = '⚠️';
            statusText = 'FULLY USED';
        } else if (validation.remaining <= 15) {
            displayClass = 'alert-warning p-2';
            statusIcon = '⚠️';
            statusText = `${validation.remaining} minutes remaining`;
        } else {
            displayClass = 'alert-success p-2';
            statusIcon = '✅';
            statusText = `${validation.remaining} minutes remaining`;
        }

        // Update alert classes
        timeValidationDisplay.className = `time-validation-display ${displayClass}`;
        const alertElement = timeValidationDisplay.querySelector('.alert');
        if (alertElement) {
            alertElement.className = `alert ${displayClass.replace('alert-', '')}`;
        }

        // Create detailed breakdown
        const breakdown = `
            <div class="time-breakdown">
                <strong>Time Slot Analysis:</strong><br>
                Available: ${validation.availableTime} minutes |
                Used: ${validation.totalTime} minutes |
                ${statusIcon} ${statusText}
            </div>
        `;

        timeValidationText.innerHTML = breakdown;

        // Add coach info if available
        if (window.selectedCoachName) {
            const coachInfo = `<div class="coach-info"><i class="fas fa-user"></i> Coach: ${window.selectedCoachName}</div>`;
            timeValidationText.innerHTML += coachInfo;
        }

    } else {
        // Hide the display if no time slot is selected
        timeValidationDisplay.style.display = 'none';
    }
}

function nextStep() {
    if (currentStep === 1) {
        if (lessons.length === 0) {
            showToast('warning', 'No Lessons', 'Please add at least one lesson.');
            return;
        }

        // Validate step 1
        if (!validateStep1()) {
            return;
        }

        if (!selectedTime) {
            showToast('warning', 'No Time Selected', 'Please select a time slot.');
            return;
        }

        // Validate total lesson time doesn't exceed available slot time
        if (window.selectedBufferMinutes !== undefined) {
            const totalLessonTime = lessons.reduce((sum, lesson) => sum + (lesson.duration || 0), 0);
            const availableTime = calculateAvailableTimeForSlot();

            if (totalLessonTime > availableTime) {
                showToast('warning', 'Time Exceeded', `Total lesson time (${totalLessonTime} minutes) exceeds the available slot time (${availableTime} minutes). Please reduce lesson duration or select a longer time slot.`);
                return;
            }
        }

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('submitBooking').style.display = 'inline-flex';
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
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';

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
            ${lessons.map((lesson, index) => {
        const lessonPrice = calculateLessonPrice(lesson);
        let pricingDetails = '';

        // Show simple pricing without breakdown
        if (!isFreeTrial) {
            pricingDetails = `$${lessonPrice}`;
        }

        return `
                    <div class="lesson-summary">
                        <p><strong>Lesson ${index + 1}:</strong> ${lesson.type} - ${lesson.duration} minutes - ${lesson.players} player${lesson.players !== 1 ? 's' : ''}</p>
                        ${lesson.ballLevel ? `<p><small>Ball Level: ${lesson.ballLevel}</small></p>` : ''}
                        ${!isFreeTrial ? `
                            <div class="pricing-breakdown">
                                <div style="display: flex; justify-content: space-between;">
                                    <span>${pricingDetails}</span>
                                    <strong>$${lessonPrice}</strong>
                    </div>
                    </div>
                        ` : ''}
                    </div>
                `;
    }).join('')}
                </div>

        <div class="summary-section">
            <h6><i class="fas fa-calculator"></i> Total</h6>
            <p><strong>Duration:</strong> ${totalDuration} minutes</p>
            ${!isFreeTrial ? `<p><strong>Total Price:</strong> $${totalPrice}</p>` : '<p><strong>Total Price:</strong> <span class="text-success">FREE TRIAL</span></p>'}
        </div>
    `;

    summaryContainer.innerHTML = summaryHTML;
}

function generateDetailedBookingSummary() {
    const totalPrice = lessons.reduce((sum, lesson) => sum + calculateLessonPrice(lesson), 0);
    const totalDuration = lessons.reduce((sum, lesson) => sum + lesson.duration, 0);
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';

        const dateStr = selectedDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    let summary = "TENNIS LESSON BOOKING DETAILS\n";
    summary += "====================================\n\n";

    // Personal Details
    summary += "PERSONAL DETAILS\n";
    summary += "-----------------\n";
    summary += `Full Name: ${document.getElementById('fullName').value.trim()}\n`;
    summary += `Email: ${document.getElementById('email').value.trim()}\n`;
    summary += `Phone: ${document.getElementById('phoneNumber').value.trim()}\n`;
    summary += `Player Type: ${document.getElementById('playerType')?.value || 'Returning'}\n`;
    summary += `Address: ${document.getElementById('address').value.trim()}\n`;
    summary += `Suburb: ${document.getElementById('suburb').value}\n`;
    summary += `City: ${document.getElementById('city').value.trim()}\n`;
    summary += `Postal Code: ${document.getElementById('postalCode').value.trim()}\n`;
    summary += `State: ${document.getElementById('state').value}\n`;
    summary += `Country: ${document.getElementById('country').value}\n\n`;

    // Booking Details
    summary += "BOOKING DETAILS\n";
    summary += "----------------\n";
    summary += `Date: ${dateStr}\n`;
    summary += `Time: ${formatTimeDisplay(selectedTime)}\n`;
    summary += `Duration: ${totalDuration} minutes\n`;
    if (!isFreeTrial) {
        // summary += `Total Price: $${totalPrice.toFixed(2)}\n\n`;
    } else {
        // summary += "Total Price: FREE TRIAL\n\n";
    }

    // Lessons Details
    summary += "LESSONS DETAILS\n";
    summary += "----------------\n";
    lessons.forEach((lesson, index) => {
        summary += `Lesson ${index + 1}:\n`;
        summary += `  Type: ${lesson.type || 'Not selected'}\n`;
        summary += `  Duration: ${lesson.duration || 0} minutes\n`;
        summary += `  Players: ${lesson.players || 0}\n`;
        summary += `  Ball Level: ${lesson.ballLevel || 'Not selected'}\n`;
        if (lesson.description) {
            summary += `  Description: ${lesson.description}\n`;
        }
        summary += "\n";
    });

    summary += `Booking Created: ${new Date().toLocaleString()}\n`;

    return summary;
}



function refreshTimeValidation() {
    if (lessons.length > 0) {
        const totalDuration = lessons.reduce((sum, lesson) => sum + (lesson.duration || 0), 0);
        updateTimeValidationDisplay(totalDuration);
    }
}



function submitBooking() {
    // Calculate total minutes from lessons
    const totalMinutes = lessons.reduce((sum, lesson) => sum + (lesson.duration || 0), 0);

    // Calculate total price
    const isFreeTrial = document.getElementById('playerType')?.value === 'FreeTrial';
    const totalPrice = isFreeTrial ? 0 : lessons.reduce((sum, lesson) => sum + calculateLessonPrice(lesson), 0);

    // Format time slot string
    const timeSlotStr = selectedDate && selectedTime ?
        `${formatTimeDisplay(selectedTime)} - ${formatTimeDisplay(minutesToTime(timeToMinutes(selectedTime) + totalMinutes))}, ${selectedDate.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        })}` : '';

    // Generate detailed booking summary
    const bookingSummary = generateDetailedBookingSummary();

    // Gather all booking details
    const bookingData = {
        playerType: document.getElementById('playerType')?.value || 'Returning',
        fullName: document.getElementById('fullName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phoneNumber: document.getElementById('phoneNumber').value.trim(),
        suburb: document.getElementById('suburb').value,
        address: document.getElementById('address').value.trim(),
        city: document.getElementById('city').value.trim(),
        postalCode: document.getElementById('postalCode').value.trim(),
        state: document.getElementById('state').value,
        country: document.getElementById('country').value,
        selectedDate: selectedDate ? formatDate(selectedDate) : null,
        selectedTimeSlot: timeSlotStr,
        totalMinutes: totalMinutes,
        bookingTotalPrice: isFreeTrial ? '0.00' : totalPrice.toFixed(2),
        totalAmount: isFreeTrial ? '0.00' : totalPrice.toFixed(2),
        bookingSummary: bookingSummary,
        selectedCoachId: window.selectedCoachId || null,
        selectedCoachName: window.selectedCoachName || null,
        selectedBufferMinutes: window.selectedBufferMinutes || 0,
        lessons: lessons.map(lesson => ({
            type: lesson.type,
            duration: lesson.duration,
            players: lesson.players,
            ballLevel: lesson.ballLevel,
            description: lesson.description
        }))
    };

    const submitBtn = document.getElementById('submitBooking');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    fetch('/api/book-appointment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(bookingData)
    })
    .then(async response => {
        const contentType = response.headers.get('content-type');
        let data;

        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            data = await response.text();
        }

        if (!response.ok) {
            // API returned an error (e.g. 422 with errors array)
            throw data; // pass data to catch
        }

        return data;
    })
    .then(data => {


        // Check if we have a URL (success case)
        if (data.url || (typeof data === 'string' && data.includes('stripe.com'))) {
            const stripeUrl = data.url || data;
            showToast('success', 'Booking Successful!', 'Redirecting to payment...');
            setTimeout(() => {
                window.location.href = stripeUrl;
            }, 1500);
        }
        // Check if it's a free trial
        else if (data.is_free_trial) {
            showToast('success', 'Booking Successful!', 'Redirecting to success page...');
            setTimeout(() => {
                window.location.href = 'https://homecourtadvantage-net.beast-hosting.com/success';
            }, 1500);
        }
        // Check if we have success flag
        else if (data.success) {
            showToast('success', 'Booking Successful!', 'Redirecting to payment...');
            setTimeout(() => {
                if (data.is_free_trial) {
                    window.location.href = 'https://homecourtadvantage-net.beast-hosting.com/success';
                } else {
                    window.location.href = data.url;
                }
            }, 1500);
        }
        // Error case
        else {
            showToast('danger', 'Booking Failed', data.message || 'Something went wrong. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);

        if (error.errors && Array.isArray(error.errors)) {
            showToast('danger', 'Booking Failed', error.errors[0]);
        } else {
            showToast('danger', 'Network Error', 'Unable to connect to server. Please check your connection and try again.');
        }

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

    // Hide time validation display
    const timeValidationDisplay = document.getElementById('timeValidationDisplay');
    if (timeValidationDisplay) timeValidationDisplay.style.display = 'none';

    // Clear selected coach info
    window.selectedCoachName = null;
    window.selectedCoachId = null;
    window.selectedBufferMinutes = null;
    window.selectedTimeSlot = null;
    window.selectedStartTime = null;
    window.selectedEndTime = null;

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

function showToast(type = 'success', title = 'Success', message = 'Everything worked!') {
    const toastEl = document.getElementById('liveToast');
    if (!toastEl) {
        // Fallback to alert if toast element doesn't exist
        alert(`${title}: ${message}`);
        return;
    }

    const toast = new bootstrap.Toast(toastEl);

    // Remove old classes
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');

    // Add new class based on type
    switch(type) {
        case 'success':
            toastEl.classList.add('bg-success');
            break;
        case 'danger':
            toastEl.classList.add('bg-danger');
            break;
        case 'warning':
            toastEl.classList.add('bg-warning');
            break;
        case 'info':
            toastEl.classList.add('bg-info');
            break;
        default:
            toastEl.classList.add('bg-success');
    }

    // Set title and message
    const titleEl = toastEl.querySelector('.toast-title');
    const messageEl = toastEl.querySelector('.toast-message');

    if (titleEl) titleEl.textContent = title;
    if (messageEl) messageEl.innerHTML = message;

    // Icon color based on type
    const icon = toastEl.querySelector('.toast-icon');
    if (icon) {
        icon.className = 'fas fa-circle me-2 toast-icon text-white';
    }

    toast.show();
}
