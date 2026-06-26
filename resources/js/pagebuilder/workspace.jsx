import React, { useEffect, useState } from 'react';

export default function Workspace({ mount }) {
  const [message, setMessage] = useState('Alturamedix Visual Page Builder');
  useEffect(() => {
    setMessage('Page Builder hazırdır.');
  }, []);
  return <div className="apb-loading">{message}</div>;
}
