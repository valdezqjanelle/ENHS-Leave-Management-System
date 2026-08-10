// Copy and paste this script into the browser console when on your application

const mockApplication = {
    id: 'mock-001',
    submittedAt: new Date().toISOString(),
    status: 'Pending',
    office: 'Schools Division Office of Isabela',
    lastName: 'Smith',
    firstName: 'John',
    middleName: 'Doe',
    dateOfFiling: '2024-05-09',
    position: 'Teacher I',
    salary: '25000',
    leaveType: {
        vacation: true,
        mandatory: false,
        sick: false,
        maternity: false,
        paternity: false,
        specialPrivilege: false,
        soloParent: false,
        study: false,
        vawc: false,
        rehabilitation: false,
        specialWomen: false,
        emergency: false,
        adoption: false,
        others: ''
    },
    leaveDetails: {
        vacation: {
            withinPhilippines: true,
            withinPhilippinesLocation: 'Baguio City',
            abroad: false,
            abroadLocation: ''
        },
        sick: {
            inHospital: false,
            illness: '',
            outPatient: false,
            outPatientIllness: ''
        },
        specialWomenIllness: '',
        study: {
            completionMasters: false,
            barBoardReview: false
        },
        other: {
            monetization: false,
            terminal: false
        }
    },
    numberOfDays: '3',
    inclusiveDates: 'May 15-17, 2024',
    commutation: {
        notRequested: true,
        requested: false
    },
    applicantSignature: 'John Doe Smith',
    attachments: [],
    certification: {
        asOf: 'May 9, 2024',
        vacation: {
            totalEarned: '10',
            lessApplication: '3',
            balance: '7'
        },
        sick: {
            totalEarned: '5',
            lessApplication: '0',
            balance: '5'
        }
    },
    recommendation: {
        forApproval: true,
        forDisapproval: false,
        disapprovalReason: ''
    },
    approved: {
        withPay: false,
        withPayDays: '',
        withoutPay: false,
        withoutPayDays: '',
        others: false,
        othersSpecify: ''
    },
    disapprovedReason: ''
};

// Get existing applications
const existingApplications = JSON.parse(localStorage.getItem('leaveApplications') || '[]');
existingApplications.push(mockApplication);
localStorage.setItem('leaveApplications', JSON.stringify(existingApplications));

console.log('✅ Mock application added successfully!');
console.log('📊 Total applications:', existingApplications.length);
console.log('👤 Applicant:', mockApplication.firstName + ' ' + mockApplication.lastName);
console.log('📅 Submitted:', new Date(mockApplication.submittedAt).toLocaleString());
console.log('📋 Status:', mockApplication.status);
console.log('🔍 Navigate to /admin-applications to see the application');
