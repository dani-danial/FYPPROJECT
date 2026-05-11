// src/Overview.js
import React, { useEffect, useState } from 'react';
import { 
  Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend 
} from 'chart.js';
import { Line, Bar } from 'react-chartjs-2';
import { Users, Activity, AlertTriangle, MapPin } from 'lucide-react';
import { collection, getDocs, getCountFromServer } from "firebase/firestore";
import { db } from './firebaseConfig'; // <--- Importing the file you just made!

// Register Charts
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

const Overview = () => {
  // --- STATE ---
  const [stats, setStats] = useState({
    totalUsers: 0,
    totalDistance: 0,
    activeRuns: 0,
    sosAlerts: 0
  });

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      // 1. Count Users
      const userSnapshot = await getCountFromServer(collection(db, "users"));
      
      // 2. Sum Distance (Looping through runs)
      const runsSnapshot = await getDocs(collection(db, "runs"));
      let distanceSum = 0;
      runsSnapshot.forEach(doc => {
        distanceSum += (doc.data().distance_km || 0);
      });

      // 3. Count SOS Alerts
      const sosSnapshot = await getCountFromServer(collection(db, "sos_alerts"));

      setStats({
        totalUsers: userSnapshot.data().count,
        totalDistance: distanceSum.toFixed(1),
        activeRuns: 5, // Placeholder (Real-time needs complex backend)
        sosAlerts: sosSnapshot.data().count
      });
      setLoading(false);

    } catch (error) {
      console.error("Error connecting to Firebase:", error);
      setLoading(false);
    }
  };

  // --- KPI CARD COMPONENT ---
  const KpiCard = ({ title, value, icon: Icon, color }) => (
    <div style={{ backgroundColor: 'white', padding: '20px', borderRadius: '12px', boxShadow: '0 2px 5px rgba(0,0,0,0.05)', display: 'flex', alignItems: 'center', minWidth: '200px' }}>
      <div style={{ backgroundColor: color, padding: '12px', borderRadius: '50%', marginRight: '16px', display: 'flex' }}>
        <Icon size={24} color="white" />
      </div>
      <div>
        <p style={{ margin: 0, color: '#666', fontSize: '14px' }}>{title}</p>
        <h3 style={{ margin: '4px 0 0 0', fontSize: '24px', fontWeight: 'bold' }}>{value}</h3>
      </div>
    </div>
  );

  if (loading) return <div style={{ padding: '40px' }}>Loading Dashboard...</div>;

  return (
    <div style={{ padding: '40px', backgroundColor: '#f4f6f8', minHeight: '100vh', fontFamily: 'Arial, sans-serif' }}>
      <h1 style={{ marginBottom: '30px', color: '#1a1a1a' }}>Admin Dashboard</h1>

      {/* KPI ROW */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '24px', marginBottom: '40px' }}>
        <KpiCard title="Total Users" value={stats.totalUsers} icon={Users} color="#3b82f6" />
        <KpiCard title="Total Distance" value={`${stats.totalDistance} km`} icon={Activity} color="#10b981" />
        <KpiCard title="Active Runs" value={stats.activeRuns} icon={MapPin} color="#8b5cf6" />
        <KpiCard title="SOS Alerts" value={stats.sosAlerts} icon={AlertTriangle} color="#ef4444" />
      </div>

      {/* CHARTS ROW */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px' }}>
        <div style={{ backgroundColor: 'white', padding: '20px', borderRadius: '12px', boxShadow: '0 2px 5px rgba(0,0,0,0.05)' }}>
          <h3>User Growth</h3>
          <Line data={{
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{ label: 'New Users', data: [12, 19, 3, 5, 2, 3], borderColor: '#3b82f6', tension: 0.3 }]
          }} />
        </div>
        
        <div style={{ backgroundColor: 'white', padding: '20px', borderRadius: '12px', boxShadow: '0 2px 5px rgba(0,0,0,0.05)' }}>
          <h3>Runs per Month</h3>
          <Bar data={{
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{ label: 'Runs', data: [65, 59, 80, 81, 56, 55], backgroundColor: 'rgba(16, 185, 129, 0.6)' }]
          }} />
        </div>
      </div>
    </div>
  );
};

export default Overview;