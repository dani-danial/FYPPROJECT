// src/firebaseConfig.js
import { initializeApp } from "firebase/app";
import { getFirestore } from "firebase/firestore";

// REPLACE THE VALUES BELOW WITH YOUR REAL FIREBASE KEYS
const firebaseConfig = {
  apiKey: "AIzaSyB3nJ7p-XerBmuPhgKO6XmJBMx7BHqvVTQ",
  authDomain: "fypruntracker.firebaseapp.com",
  projectId: "fypruntracker",
  storageBucket: "fypruntracker.firebasestorage.app",
  messagingSenderId: "804029606132",
  appId: "1:804029606132:web:311f4d61559fdee6d0d6a1",
  measurementId: "G-DNRJP4E8DT"
};
// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Export the database so other files can use it
export const db = getFirestore(app);