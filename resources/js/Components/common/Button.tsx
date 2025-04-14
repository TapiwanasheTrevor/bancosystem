import React from 'react';
import { LucideIcon } from 'lucide-react';

interface ButtonProps {
  children: React.ReactNode;
  onClick: () => void;
  variant?: 'default' | 'primary' | 'outline' | 'danger';
  icon?: LucideIcon;
  disabled?: boolean;
  fullWidth?: boolean;
  type?: 'button' | 'submit' | 'reset';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const Button: React.FC<ButtonProps> = ({ 
  children, 
  onClick, 
  variant = 'default', 
  icon: Icon, 
  disabled = false,
  fullWidth = false,
  type = 'button',
  size = 'md',
  className = ''
}) => {
  const sizeClasses = {
    sm: 'py-1.5 px-3 text-sm',
    md: 'py-2.5 px-4',
    lg: 'py-3 px-6 text-lg'
  };

  const variantClasses = {
    default: 'bg-white border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 text-gray-800',
    primary: 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:from-emerald-600 hover:to-emerald-700',
    outline: 'bg-transparent border border-emerald-500 text-emerald-600 hover:bg-emerald-50',
    danger: 'bg-red-500 text-white hover:bg-red-600'
  };

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`
        ${sizeClasses[size]}
        ${variantClasses[variant]}
        ${fullWidth ? 'w-full' : ''}
        rounded-xl transition-all duration-300 flex items-center justify-between
        ${disabled ? 'opacity-50 cursor-not-allowed' : ''}
        ${className}
      `}
    >
      <span className={`flex-1 text-left ${Icon ? 'mr-2' : ''}`}>{children}</span>
      {Icon && <Icon className="w-5 h-5 flex-shrink-0" />}
    </button>
  );
};

export default Button;