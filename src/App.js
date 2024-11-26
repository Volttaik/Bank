import React, { useEffect, useState } from 'react';
import './App.css';

function App() {
  const [pageContent, setPageContent] = useState(null);

  useEffect(() => {
    // Fetch the index.php page content from the PHP server
    fetch('http://localhost:8000/index.php')
      .then((response) => response.text())
      .then((data) => setPageContent(data))
      .catch((error) => console.error('Error fetching PHP content:', error));
  }, []);

  return (
    <div className="App">
      <header className="App-header">
        {pageContent ? (
          <div dangerouslySetInnerHTML={{ __html: pageContent }} />
        ) : (
          <p>Loading...</p>
        )}
      </header>
    </div>
  );
}

export default App;
