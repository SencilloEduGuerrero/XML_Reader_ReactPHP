import './App.css'
import { useState } from 'react';

function App() {
  // UseState - React
  const [file, setFile] = useState(null);

  // Prevent refresh the page
  function handleFile(e) {
    setFile(e.target.files[0]);
  }

  // Function that sent the XML and prevent empty XML.
  async function readXML() {
    if (!file) {
      alert("Select a XML file");
      return;
    }

    const formData = new FormData();

    formData.append("xml", file);

    // I sent to my PHP.
    const response = await fetch(
      'http://localhost:8000/src/methods/ReadXML.php',
      {
        method: 'POST',
        body: formData
      }
    );

    const data = await response.json();

    console.log(data);
  }

  return (
    <>
      <div className="header">
        <h1>XML Reader Version 1</h1>
        <hr></hr>
      </div>

      <div className="main-container">
          <div>
            <label htmlFor="xml-file">Only XML</label>
            <br />
            <input onChange={handleFile} name="xml-file" accept=".xml" className="input-xml" type="file"></input>
          </div>
          <button onClick={readXML} className="button-submit">Upload</button>
      </div>
    </>
  )
}

export default App
