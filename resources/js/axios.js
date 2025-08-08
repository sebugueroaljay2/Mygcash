// import axios from 'axios';

// const axiosInstance = axios.create({
//     baseURL: import.meta.env.VITE_API_BASE_URL,
//     withCredentials: true,  // optional, kung may cookies/auth
// });

// export default axiosInstance;


import axios from 'axios'

axios.defaults.withCredentials = true
axios.defaults.baseURL = 'https://tame-jacynth-aljaydev-53c82294.koyeb.app'; // change kung iba URL mo

export default axios